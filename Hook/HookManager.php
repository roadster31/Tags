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

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 *
 * Creation date: 23/03/2015 12:09
 */

namespace Tags\Hook;

use Tags\Model\Tags as TagsModel;
use Tags\Model\TagsQuery;
use Tags\Tags;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Translation\Translator;
use Thelia\Tools\URL;

class HookManager extends BaseHook
{
    public function onMainTopMenuTools(HookRenderBlockEvent $event): void
    {
        $event->add(
            [
                'id' => 'tools_menu_tags',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/Tags'),
                'title' => "Tags"
            ]
        );
    }

    private function processFieldHook(HookRenderEvent $event, $sourceType, $sourceId): void
    {
        $tags = TagsQuery::create()
            ->filterBySource($sourceType)
            ->filterBySourceId($sourceId)
            ->find();

        $tagValue = '';

        /** @var TagsModel $tag */
        foreach ($tags as $tag) {
            $tagValue .= $tag->getTag() . ', ';
        }

        $event->add(
            $this->render(
                "tags-includes/generic-tag-definition.html",
                [
                    'tags' => trim($tagValue, ', ')
                ]
            )
        );
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $event->add(
            $this->render("tags-includes/module-configuration.html")
        );
    }

    public function onProductEditRightColumnBottom(HookRenderEvent $event): void
    {
        $this->processFieldHook($event, 'product', $event->getArgument('product_id'));
    }

    public function onCategoryEditRightColumnBottom(HookRenderEvent $event): void
    {
        $this->processFieldHook($event, 'category', $event->getArgument('category_id'));
    }

    public function onContentEditRightColumnBottom(HookRenderEvent $event): void
    {
        $this->processFieldHook($event, 'content', $event->getArgument('content_id'));
    }

    public function onFolderEditRightColumnBottom(HookRenderEvent $event): void
    {
        $this->processFieldHook($event, 'folder', $event->getArgument('folder_id'));
    }

    public function onBrandEditRightColumnBottom(HookRenderEvent $event): void
    {
        $this->processFieldHook($event, 'brand', $event->getArgument('brand_id'));
    }

    public function addTagFieldJs(HookRenderEvent $event): void
    {
        $imageJs = $this->addJS("tags-includes/assets/js/addFieldInForm.js");
        $event->add($imageJs);
    }

    public function hiddenTagTemplate(HookRenderEvent $event): void
    {
        $help = Translator::getInstance()->trans('Enter one or more tags, separated by commas.', [],Tags::DOMAIN_NAME);
        $url = URL::getInstance()->absoluteUrl('/admin/module/Tags');
        $link = Translator::getInstance()->trans('View all defined tags', [],Tags::DOMAIN_NAME);
        $event->add($this->render("tags-includes/tag-field.html",
            [
                'help' => $help,
                'url' => $url,
                'link' => $link
            ]
        ));
    }
}
