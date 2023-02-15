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
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Translation\Translator;
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
use Thelia\Model\Map\ProductSaleElementsProductImageTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Tools\URL;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

class EventManager implements EventSubscriberInterface
{
    protected $request;

    public function __construct(RequestStack $request)
    {
        $this->request = $request->getCurrentRequest();
    }

    public static function getSubscribedEvents()
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

    public function addLoopArgDefinition(LoopExtendsArgDefinitionsEvent $event)
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

    public function contentLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->setupLoopBuildModelCriteria(ContentTableMap::ID, 'content', $event);
    }

    public function categoryLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->setupLoopBuildModelCriteria(CategoryTableMap::ID, 'category', $event);
    }

    public function productLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->setupLoopBuildModelCriteria(ProductTableMap::ID, 'product', $event);
    }

    public function folderLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->setupLoopBuildModelCriteria(FolderTableMap::ID, 'folder', $event);
    }

    public function brandLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->setupLoopBuildModelCriteria(BrandTableMap::ID, 'brand', $event);
    }

    public function imageLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        switch ($this->getLoopObjectType($event->getLoop()->getArgumentCollection())) {
            case 'product':
                $this->setupLoopBuildModelCriteria(ProductImageTableMap::ID, 'product_image', $event);
                break;
            case 'category':
                $this->setupLoopBuildModelCriteria(CategoryImageTableMap::ID, 'category_image', $event);
                break;
            case 'content':
                $this->setupLoopBuildModelCriteria(ContentImageTableMap::ID, 'content_image', $event);
                break;
            case 'folder':
                $this->setupLoopBuildModelCriteria(FolderImageTableMap::ID, 'folder_image', $event);
                break;
            case 'brand':
                $this->setupLoopBuildModelCriteria(BrandImageTableMap::ID, 'brand_image', $event);
                break;
            default:
                break;
        }
    }

    public function documentLoopBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        switch ($this->getLoopObjectType($event->getLoop()->getArgumentCollection())) {
            case 'product':
                $this->setupLoopBuildModelCriteria(ProductDocumentTableMap::ID, 'product_document', $event);
                break;
            case 'category':
                $this->setupLoopBuildModelCriteria(CategoryDocumentTableMap::ID, 'category_document', $event);
                break;
            case 'content':
                $this->setupLoopBuildModelCriteria(ContentDocumentTableMap::ID, 'content_document', $event);
                break;
            case 'folder':
                $this->setupLoopBuildModelCriteria(FolderDocumentTableMap::ID, 'folder_document', $event);
                break;
            case 'brand':
                $this->setupLoopBuildModelCriteria(BrandDocumentTableMap::ID, 'brand_document', $event);
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
    protected function getLoopObjectType(ArgumentCollection $argumentCollection)
    {
        static $knownObjects = [
            'product',
            'category',
            'content',
            'folder',
            'brand'
        ];

        $objectType = $argumentCollection->get('source')->getValue();

        if (empty($objectType)) {
            foreach ($knownObjects as $object) {
                if (! empty($argumentCollection->get($object)->getValue())) {
                    return $object;
                }
            }
        }

        return null;
    }

    protected function setupLoopBuildModelCriteria($leftTableFieldName, $loopType, LoopExtendsBuildModelCriteriaEvent $event)
    {
        $this->handleTagArgument($leftTableFieldName, $loopType, $event);
        $this->handleExcludeTagArgument($leftTableFieldName, $loopType, $event);
    }

    protected function handleTagArgument($leftTableFieldName, $loopType, LoopExtendsBuildModelCriteriaEvent $event)
    {
        $tags = $event->getLoop()->getArgumentCollection()->get('tag')->getValue();

        if (!empty($tags)) {
            $search = $event->getModelCriteria();

            $search
                ->addJoin($leftTableFieldName, TagsTableMap::SOURCE_ID, Criteria::LEFT_JOIN) // Can also be left/right
                ->add(TagsTableMap::SOURCE, $loopType, Criteria::EQUAL)
            ;

            $matchMode = $event->getLoop()->getArgumentCollection()->get('tag_match_mode')->getValue();

            if ('exact' === $matchMode) {
                $search->add(TagsTableMap::TAG, $tags, Criteria::IN);
            } else {
                foreach ($tags as $tag) {
                    $search->add(TagsTableMap::TAG, "%$tag%", Criteria::LIKE);
                }
            }
        }
    }

    protected function handleExcludeTagArgument($leftTableId, $loopType, LoopExtendsBuildModelCriteriaEvent $event)
    {
        $excludeTags = $event->getLoop()->getArgumentCollection()->get('exclude_tag')->getValue();

        if (!empty($excludeTags)) {
            $search = $event->getModelCriteria();

            $tagJoin = new Join($leftTableId, TagsTableMap::SOURCE_ID, Criteria::LEFT_JOIN);

            $search
                ->addJoinObject($tagJoin, 'any_table_tags_join')
                ->addJoinCondition(
                    'any_table_tags_join',
                    '('
                    . TagsTableMap::SOURCE . Criteria::EQUAL . ' \'' . $loopType . '\' '
                    . Criteria::LOGICAL_OR . ' '
                    . TagsTableMap::SOURCE . Criteria::ISNULL
                    . ') '
                )
            ;

            $search->where(
                ' ('
                . TagsTableMap::TAG . Criteria::NOT_IN . ' (\'' . implode("','", $excludeTags) . '\') '
                . Criteria::LOGICAL_OR . ' '
                . TagsTableMap::TAG . Criteria::ISNULL
                . ')'
            );
        }
    }

    public function addFieldToForm(TheliaFormEvent $event)
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

    public function processTags(ActionEvent $event, $source, $sourceId)
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

    public function processProductFields(ProductEvent $event)
    {
        if ($event->hasProduct()) {
            $this->processTags($event, 'product', $event->getProduct()->getId());
        }
    }

    public function processCategoryFields(CategoryEvent $event)
    {
        if ($event->hasCategory()) {
            $this->processTags($event, 'category', $event->getCategory()->getId());
        }
    }

    public function processFolderFields(FolderEvent $event)
    {
        if ($event->hasFolder()) {
            $this->processTags($event, 'folder', $event->getFolder()->getId());
        }
    }

    public function processContentFields(ContentEvent $event)
    {
        if ($event->hasContent()) {
            $this->processTags($event, 'content', $event->getContent()->getId());
        }
    }

    public function processBrandFields(BrandEvent $event)
    {
        if ($event->hasBrand()) {
            $this->processTags($event, 'brand', $event->getBrand()->getId());
        }
    }

    public function processImageFields(FileCreateOrUpdateEvent $event)
    {
        if (null !== $model = $event->getModel()) {
            switch (get_class($model)) {
                case 'Thelia\Model\ProductImage':
                    $event->tags = $this->request->request->get('thelia_product_image_modification')['tags'];
                    $this->processTags($event, 'product_image', $model->getId());
                    break;
                case 'Thelia\Model\CategoryImage':
                    $event->tags = $this->request->request->get('thelia_category_image_modification')['tags'];
                    $this->processTags($event, 'category_image', $model->getId());
                    break;
                case 'Thelia\Model\ContentImage':
                    $event->tags = $this->request->request->get('thelia_content_image_modification')['tags'];
                    $this->processTags($event, 'content_image', $model->getId());
                    break;
                case 'Thelia\Model\FolderImage':
                    $event->tags = $this->request->request->get('thelia_folder_image_modification')['tags'];
                    $this->processTags($event, 'folder_image', $model->getId());
                    break;
                case 'Thelia\Model\BrandImage':
                    $event->tags = $this->request->request->get('thelia_brand_image_modification')['tags'];
                    $this->processTags($event, 'brand_image', $model->getId());
                    break;
                default:
                    break;
            }
        }
    }

    public function processDocumentFields(FileCreateOrUpdateEvent $event)
    {
        if (null !== $model = $event->getModel()) {
            switch (get_class($model)) {
                case 'Thelia\Model\ProductDocument':
                    $event->tags = $this->request->request->get('thelia_product_document_modification')['tags'];
                    $this->processTags($event, 'product_document', $model->getId());
                    break;
                case 'Thelia\Model\CategoryDocument':
                    $event->tags = $this->request->request->get('thelia_category_document_modification')['tags'];
                    $this->processTags($event, 'category_document', $model->getId());
                    break;
                case 'Thelia\Model\ContentDocument':
                    $event->tags = $this->request->request->get('thelia_content_document_modification')['tags'];
                    $this->processTags($event, 'content_document', $model->getId());
                    break;
                case 'Thelia\Model\FolderDocument':
                    $event->tags = $this->request->request->get('thelia_folder_document_modification')['tags'];
                    $this->processTags($event, 'folder_document', $model->getId());
                    break;
                case 'Thelia\Model\BrandDocument':
                    $event->tags = $this->request->request->get('thelia_brand_document_modification')['tags'];
                    $this->processTags($event, 'brand_document', $model->getId());
                    break;
                default:
                    break;
            }
        }
    }

    public function deleteProduct(ProductDeleteEvent $event)
    {
        TagsQuery::create()->filterBySource('product')->filterBySourceId($event->getProductId())->delete();
    }

    public function deleteCategory(CategoryDeleteEvent $event)
    {
        TagsQuery::create()->filterBySource('category')->filterBySourceId($event->getCategoryId())->delete();
    }

    public function deleteContent(ContentDeleteEvent $event)
    {
        TagsQuery::create()->filterBySource('content')->filterBySourceId($event->getContentId())->delete();
    }

    public function deleteFolder(FolderDeleteEvent $event)
    {
        TagsQuery::create()->filterBySource('folder')->filterBySourceId($event->getFolderId())->delete();
    }

    public function deleteBrand(BrandDeleteEvent $event)
    {
        TagsQuery::create()->filterBySource('brand')->filterBySourceId($event->getBrandId())->delete();
    }
}
