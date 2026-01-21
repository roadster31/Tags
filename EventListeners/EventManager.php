<?php
/*************************************************************************************/
/*                                                                                   */
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*                                                                                   */
/*************************************************************************************/

namespace Tags\EventListeners;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Tags\Model\Map\TagsTableMap;
use Tags\Model\Base\TagsQuery;
use Tags\Tags;
use Thelia\Core\Event\ActionEvent;
use Thelia\Core\Event\Brand\BrandDeleteEvent;
use Thelia\Core\Event\Brand\BrandEvent;
use Thelia\Core\Event\Category\CategoryDeleteEvent;
use Thelia\Core\Event\Category\CategoryEvent;
use Thelia\Core\Event\Content\ContentDeleteEvent;
use Thelia\Core\Event\Content\ContentEvent;
use Thelia\Core\Event\File\FileCreateOrUpdateEvent;
use Thelia\Core\Event\Folder\FolderDeleteEvent;
use Thelia\Core\Event\Folder\FolderEvent;
use Thelia\Core\Event\Loop\LoopExtendsArgDefinitionsEvent;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\Product\ProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Translation\Translator;
use Thelia\Files\FileModelInterface;
use Thelia\Model\Map\BrandDocumentTableMap;
use Thelia\Model\Map\BrandImageTableMap;
use Thelia\Model\Map\BrandTableMap;
use Thelia\Model\Map\CategoryDocumentTableMap;
use Thelia\Model\Map\CategoryImageTableMap;
use Thelia\Model\Map\CategoryTableMap;
use Thelia\Model\Map\ContentDocumentTableMap;
use Thelia\Model\Map\ContentImageTableMap;
use Thelia\Model\Map\ContentTableMap;
use Thelia\Model\Map\FolderDocumentTableMap;
use Thelia\Model\Map\FolderImageTableMap;
use Thelia\Model\Map\FolderTableMap;
use Thelia\Model\Map\ProductDocumentTableMap;
use Thelia\Model\Map\ProductImageTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Tools\URL;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

class EventManager implements EventSubscriberInterface
{

    public function __construct(protected RequestStack $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_DELETE  => [ 'deleteProduct' ],
            TheliaEvents::CATEGORY_DELETE => [ 'deleteCategory' ],
            TheliaEvents::CONTENT_DELETE  => [ 'deleteContent' ],
            TheliaEvents::FOLDER_DELETE   => [ 'deleteFolder' ],
            TheliaEvents::BRAND_DELETE    => [ 'deleteBrand' ],

            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_brand_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_brand_modification" => ['addFieldToForm', 128],

            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_image_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_image_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_image_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_image_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_brand_image_modification" => ['addFieldToForm', 128],

            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_document_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_document_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_document_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_document_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_brand_document_modification" => ['addFieldToForm', 128],

            TheliaEvents::PRODUCT_UPDATE  => ['processProductFields', 100],
            TheliaEvents::PRODUCT_CREATE  => ['processProductFields', 100],

            TheliaEvents::CATEGORY_CREATE  => ['processCategoryFields', 100],
            TheliaEvents::CATEGORY_UPDATE  => ['processCategoryFields', 100],

            TheliaEvents::FOLDER_CREATE  => ['processFolderFields', 100],
            TheliaEvents::FOLDER_UPDATE  => ['processFolderFields', 100],

            TheliaEvents::CONTENT_CREATE  => ['processContentFields', 100],
            TheliaEvents::CONTENT_UPDATE  => ['processContentFields', 100],

            TheliaEvents::BRAND_CREATE  => ['processBrandFields', 100],
            TheliaEvents::BRAND_UPDATE  => ['processBrandFields', 100],

            TheliaEvents::IMAGE_UPDATE => ['processImageFields', 100],

            TheliaEvents::DOCUMENT_UPDATE => ['processDocumentFields', 100],

            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'content') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'product') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'folder') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'category') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'brand') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'image') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'document') => ['addLoopArgDefinition', 128],

            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'content') => ['contentLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'product') => ['productLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'folder')  => ['folderLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'category') => ['categoryLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'brand') => ['brandLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'image') => ['imageLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'document') => ['documentLoopBuildModelCriteria', 128],
        ];
    }

    public function addLoopArgDefinition(LoopExtendsArgDefinitionsEvent $event): void
    {
        $argument = $event->getArgumentCollection();
        $argument
            ->addArgument(
                Argument::createAnyListTypeArgument('tag')
            )
            ->addArgument(
                Argument::createAnyListTypeArgument('exclude_tag')
            )
            ->addArgument(
                new Argument(
                    'tag_match_mode',
                    new TypeCollection(new EnumType([ 'exact', 'partial' ])),
                    'exact'
                )
            )
        ;
    }

    /**
     * @throws PropelException
     */
    public function contentLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->setupLoopBuildModelCriteria(ContentTableMap::COL_ID, 'content', $event);
    }

    /**
     * @throws PropelException
     */
    public function categoryLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->setupLoopBuildModelCriteria(CategoryTableMap::COL_ID, 'category', $event);
    }

    /**
     * @throws PropelException
     */
    public function productLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->setupLoopBuildModelCriteria(ProductTableMap::COL_ID, 'product', $event);
    }

    /**
     * @throws PropelException
     */
    public function folderLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->setupLoopBuildModelCriteria(FolderTableMap::COL_ID, 'folder', $event);
    }

    /**
     * @throws PropelException
     */
    public function brandLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->setupLoopBuildModelCriteria(BrandTableMap::COL_ID, 'brand', $event);
    }

    /**
     * @throws PropelException
     */
    public function imageLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        switch ($this->getLoopObjectType($event->getLoop()?->getArgumentCollection())) {
            case 'product':
                $this->setupLoopBuildModelCriteria(ProductImageTableMap::COL_ID, 'product_image', $event);
                break;
            case 'category':
                $this->setupLoopBuildModelCriteria(CategoryImageTableMap::COL_ID, 'category_image', $event);
                break;
            case 'content':
                $this->setupLoopBuildModelCriteria(ContentImageTableMap::COL_ID, 'content_image', $event);
                break;
            case 'folder':
                $this->setupLoopBuildModelCriteria(FolderImageTableMap::COL_ID, 'folder_image', $event);
                break;
            case 'brand':
                $this->setupLoopBuildModelCriteria(BrandImageTableMap::COL_ID, 'brand_image', $event);
                break;
            default:
                break;
        }
    }

    /**
     * @throws PropelException
     */
    public function documentLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        switch ($this->getLoopObjectType($event->getLoop()?->getArgumentCollection())) {
            case 'product':
                $this->setupLoopBuildModelCriteria(ProductDocumentTableMap::COL_ID, 'product_document', $event);
                break;
            case 'category':
                $this->setupLoopBuildModelCriteria(CategoryDocumentTableMap::COL_ID, 'category_document', $event);
                break;
            case 'content':
                $this->setupLoopBuildModelCriteria(ContentDocumentTableMap::COL_ID, 'content_document', $event);
                break;
            case 'folder':
                $this->setupLoopBuildModelCriteria(FolderDocumentTableMap::COL_ID, 'folder_document', $event);
                break;
            case 'brand':
                $this->setupLoopBuildModelCriteria(BrandDocumentTableMap::COL_ID, 'brand_document', $event);
                break;
            default:
                break;
        }
    }

    /**
     * Guess object type for image and doucment loops
     *
     * @param ArgumentCollection $argumentCollection
     * @return string|null
     */
    protected function getLoopObjectType(ArgumentCollection $argumentCollection): ?string
    {
        static $knownObjects = [
            'product',
            'category',
            'content',
            'folder',
            'brand'
        ];

        $objectType = $argumentCollection->get('source')?->getValue();

        if (empty($objectType)) {
            foreach ($knownObjects as $object) {
                if (! empty($argumentCollection->get($object)?->getValue())) {
                    return $object;
                }
            }
        }

        return null;
    }

    /**
     * @throws PropelException
     */
    protected function setupLoopBuildModelCriteria($leftTableFieldName, $loopType, LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $this->handleTagArgument($leftTableFieldName, $loopType, $event);
        $this->handleExcludeTagArgument($leftTableFieldName, $loopType, $event);
    }

    protected function handleTagArgument($leftTableFieldName, $loopType, LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $tags = $event->getLoop()?->getArgumentCollection()->get('tag')?->getValue();

        if (!empty($tags)) {
            $search = $event->getModelCriteria();

            $search
                ->addJoin($leftTableFieldName, TagsTableMap::COL_SOURCE_ID, Criteria::LEFT_JOIN) // Can also be left/right
                ->add(TagsTableMap::COL_SOURCE, $loopType, Criteria::EQUAL)
            ;

            $matchMode = $event->getLoop()?->getArgumentCollection()->get('tag_match_mode')?->getValue();

            if ('exact' === $matchMode) {
                $search->add(TagsTableMap::COL_TAG, $tags, Criteria::IN);
            } else {
                foreach ($tags as $tag) {
                    $search->add(TagsTableMap::COL_TAG, "%$tag%", Criteria::LIKE);
                }
            }
        }
    }

    /**
     * @throws PropelException
     */
    protected function handleExcludeTagArgument($leftTableId, $loopType, LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $excludeTags = $event->getLoop()?->getArgumentCollection()->get('exclude_tag')?->getValue();

        if (!empty($excludeTags)) {
            $search = $event->getModelCriteria();

            $tagJoin = new Join($leftTableId, TagsTableMap::COL_SOURCE_ID, Criteria::LEFT_JOIN);

            $search
                ->addJoinObject($tagJoin, 'any_table_tags_join')
                ->addJoinCondition(
                    'any_table_tags_join',
                    '('
                    . TagsTableMap::COL_SOURCE . Criteria::EQUAL . ' \'' . $loopType . '\' '
                    . Criteria::LOGICAL_OR . ' '
                    . TagsTableMap::COL_SOURCE . Criteria::ISNULL
                    . ') '
                )
            ;

            $search->where(
                ' ('
                . TagsTableMap::COL_TAG . Criteria::NOT_IN . ' (\'' . implode("','", $excludeTags) . '\') '
                . Criteria::LOGICAL_OR . ' '
                . TagsTableMap::COL_TAG . Criteria::ISNULL
                . ')'
            );
        }
    }

    public function addFieldToForm(TheliaFormEvent $event): void
    {
        $event->getForm()->getFormBuilder()->add(
            'tags',
            TextType::class,
            [
                'required' => false,
                'label' => Translator::getInstance()->trans(
                    'Tags',
                    [],
                    Tags::DOMAIN_NAME
                ),
                'label_attr'  => [
                    'help' => Translator::getInstance()->trans(
                        'Enter one or more tags, separated by commas. <a href="%url%">View all defined tags</a>.',
                        [ '%url%' => URL::getInstance()->absoluteUrl('/admin/module/Tags') ],
                        Tags::DOMAIN_NAME
                    )
                ]
            ]
        );
    }

    /**
     * @throws PropelException
     */
    public function processTags(ActionEvent $event, $source, $sourceId): void
    {
        // Utilise le principe NON DOCUMENTE qui dit que si une form bindée à un event trouve
        // un champ absent de l'event, elle le rend accessible à travers une méthode magique.
        // (cf. ActionEvent::bindForm())

        // Delete existing values
        TagsQuery::create()->filterBySource($source)->filterBySourceId($sourceId)->delete();

        $tags = trim($event->tags);

        if (! empty($tags)) {
            $tagsValues = explode(',', $tags);

            if (! empty($tagsValues)) {
                foreach ($tagsValues as $tagValue) {
                    if (! empty($tagValue)) {
                        $tags = new \Tags\Model\Tags();

                        $tags
                            ->setSource($source)
                            ->setSourceId($sourceId)
                            ->setTag(trim($tagValue))->save();
                    }
                }
            }
        }
    }

    /**
     * @throws PropelException
     */
    public function processProductFields(ProductEvent $event): void
    {
        if ($event->hasProduct()) {
            $this->processTags($event, 'product', $event->getProduct()?->getId());
        }
    }

    public function processCategoryFields(CategoryEvent $event): void
    {
        if ($event->hasCategory()) {
            try {
                $this->processTags($event, 'category', $event->getCategory()?->getId());
            } catch (PropelException) {
                // Nothing useful to do...
            }
        }
    }

    /**
     * @throws PropelException
     */
    public function processFolderFields(FolderEvent $event): void
    {
        if ($event->hasFolder()) {
            $this->processTags($event, 'folder', $event->getFolder()?->getId());
        }
    }

    /**
     * @throws PropelException
     */
    public function processContentFields(ContentEvent $event): void
    {
        if ($event->hasContent()) {
            $this->processTags($event, 'content', $event->getContent()?->getId());
        }
    }

    /**
     * @throws PropelException
     */
    public function processBrandFields(BrandEvent $event): void
    {
        if ($event->hasBrand()) {
            $this->processTags($event, 'brand', $event->getBrand()?->getId());
        }
    }

    /**
     * @throws PropelException
     */
    public function processImageFields(FileCreateOrUpdateEvent $event): void
    {
        if (null === $model = $event->getModel()) {
            return;
        }

        switch (get_class($model)) {
            case 'Thelia\Model\ProductImage':
                $this->processImageOrDocumentKindFields('product_image', $event, $model);
                return;
            case 'Thelia\Model\CategoryImage':
                $this->processImageOrDocumentKindFields('category_image', $event, $model);
                return;
            case 'Thelia\Model\ContentImage':
                $this->processImageOrDocumentKindFields('content_image', $event, $model);
                return;
            case 'Thelia\Model\FolderImage':
                $this->processImageOrDocumentKindFields('folder_image', $event, $model);
                return;
            case 'Thelia\Model\BrandImage':
                $this->processImageOrDocumentKindFields('brand_image', $event, $model);
                return;
        }
    }

    /**
     * @throws PropelException
     */
    public function processDocumentFields(FileCreateOrUpdateEvent $event): void
    {
        if (null === $model = $event->getModel()) {
            return;
        }

        switch (get_class($model)) {
            case 'Thelia\Model\ProductDocument':
                $this->processImageOrDocumentKindFields('product_document', $event, $model);
                return;
            case 'Thelia\Model\CategoryDocument':
                $this->processImageOrDocumentKindFields('category_document', $event, $model);
                return;
            case 'Thelia\Model\ContentDocument':
                $this->processImageOrDocumentKindFields('content_document', $event, $model);
                return;
            case 'Thelia\Model\FolderDocument':
                $this->processImageOrDocumentKindFields('folder_document', $event, $model);
                return;
            case 'Thelia\Model\BrandDocument':
                $this->processImageOrDocumentKindFields('brand_document', $event, $model);
                return;
        }
    }

    /**
     * @throws PropelException
     */
    protected function processImageOrDocumentKindFields(string $kind, FileCreateOrUpdateEvent $event, FileModelInterface $model): void
    {
        if (null === $event->tags = $this->requestStack->getCurrentRequest()?->get('thelia_'.$kind.'_modification')['tags'] ?? null) {
            return;
        }

        $this->processTags($event, $kind, $model->getId());
    }

    /**
     * @throws PropelException
     */
    public function deleteProduct(ProductDeleteEvent $event): void
    {
        TagsQuery::create()->filterBySource('product')->filterBySourceId($event->getProductId())->delete();
    }

    /**
     * @throws PropelException
     */
    public function deleteCategory(CategoryDeleteEvent $event): void
    {
        TagsQuery::create()->filterBySource('category')->filterBySourceId($event->getCategoryId())->delete();
    }

    /**
     * @throws PropelException
     */
    public function deleteContent(ContentDeleteEvent $event): void
    {
        TagsQuery::create()->filterBySource('content')->filterBySourceId($event->getContentId())->delete();
    }

    /**
     * @throws PropelException
     */
    public function deleteFolder(FolderDeleteEvent $event): void
    {
        TagsQuery::create()->filterBySource('folder')->filterBySourceId($event->getFolderId())->delete();
    }

    /**
     * @throws PropelException
     */
    public function deleteBrand(BrandDeleteEvent $event): void
    {
        TagsQuery::create()->filterBySource('brand')->filterBySourceId($event->getBrandId())->delete();
    }
}
