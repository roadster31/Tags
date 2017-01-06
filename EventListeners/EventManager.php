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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tags\Model\TagsQuery;
use Tags\Tags;
use Thelia\Core\Event\ActionEvent;
use Thelia\Core\Event\Category\CategoryDeleteEvent;
use Thelia\Core\Event\Category\CategoryEvent;
use Thelia\Core\Event\Content\ContentDeleteEvent;
use Thelia\Core\Event\Content\ContentEvent;
use Thelia\Core\Event\Folder\FolderDeleteEvent;
use Thelia\Core\Event\Folder\FolderEvent;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\Product\ProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\Translation\Translator;
use Thelia\Tools\URL;

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
        ];
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
