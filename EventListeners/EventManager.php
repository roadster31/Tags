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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tags\Model\Map\TagsTableMap;
use Tags\Model\TagsQuery;
use Tags\Tags;
use Thelia\Core\Event\ActionEvent;
use Thelia\Core\Event\Category\CategoryDeleteEvent;
use Thelia\Core\Event\Category\CategoryEvent;
use Thelia\Core\Event\Content\ContentDeleteEvent;
use Thelia\Core\Event\Content\ContentEvent;
use Thelia\Core\Event\Folder\FolderDeleteEvent;
use Thelia\Core\Event\Folder\FolderEvent;
use Thelia\Core\Event\Loop\LoopExtendsArgDefinitionsEvent;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\Product\ProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Map\CategoryTableMap;
use Thelia\Model\Map\ContentTableMap;
use Thelia\Model\Map\FolderTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Tools\URL;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

class EventManager implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::PRODUCT_DELETE  => [ 'deleteProduct' ],
            TheliaEvents::CATEGORY_DELETE => [ 'deleteCategory' ],
            TheliaEvents::CONTENT_DELETE  => [ 'deleteContent' ],
            TheliaEvents::FOLDER_DELETE   => [ 'deleteFolder' ],

            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_content_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_category_modification" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_creation" => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_folder_modification" => ['addFieldToForm', 128],

            TheliaEvents::PRODUCT_UPDATE  => ['processProductFields', 100],
            TheliaEvents::PRODUCT_CREATE  => ['processProductFields', 100],

            TheliaEvents::CATEGORY_CREATE  => ['processCategoryFields', 100],
            TheliaEvents::CATEGORY_UPDATE  => ['processCategoryFields', 100],

            TheliaEvents::FOLDER_CREATE  => ['processFolderFields', 100],
            TheliaEvents::FOLDER_UPDATE  => ['processFolderFields', 100],

            TheliaEvents::CONTENT_CREATE  => ['processContentFields', 100],
            TheliaEvents::CONTENT_UPDATE  => ['processContentFields', 100],

            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'content') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'product') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'folder') => ['addLoopArgDefinition', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'category') => ['addLoopArgDefinition', 128],

            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'content') => ['contentLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'product') => ['productLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'folder')  => ['folderLoopBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'category') => ['categoryLoopBuildModelCriteria', 128],
        ];
    }

    public function addLoopArgDefinition(LoopExtendsArgDefinitionsEvent $event)
    {
        $argument = $event->getArgumentCollection();
        $argument->addArgument(
            Argument::createAnyListTypeArgument('tag')
        )->addArgument(
            new Argument(
                'tag_match_mode',
                new TypeCollection(new EnumType([ 'exact', 'partial' ])),
                'exact'
            )
        );
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

    protected function setupLoopBuildModelCriteria($leftTableFieldName, $loopType, LoopExtendsBuildModelCriteriaEvent $event)
    {
        $tags = $event->getLoop()->getArgumentCollection()->get('tag')->getValue();

        if (! empty($tags)) {
            $search = $event->getModelCriteria();

            $search
                ->addJoin($leftTableFieldName, TagsTableMap::SOURCE_ID, Criteria::LEFT_JOIN) // Can also be left/right
                ->add(TagsTableMap::SOURCE, $loopType, Criteria::EQUAL);
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

    public function addFieldToForm(TheliaFormEvent $event)
    {
        $event->getForm()->getFormBuilder()->add(
            'tags',
            'text',
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
        TagsQuery::create()->filterBySource('content')->filterBySourceId($event->getFolderId())->delete();
    }
}
