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

use Tags\Model\Tags;
use Tags\Model\TagsQuery;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class HookManager extends BaseHook
{
    public function onMainTopMenuTools(HookRenderBlockEvent $event)
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

    private function processFieldHook(HookRenderEvent $event, $sourceType, $sourceId)
    {
        $tags = TagsQuery::create()
            ->filterBySource($sourceType)
            ->filterBySourceId($sourceId)
            ->find();

        $tagValue = '';

        /** @var Tags $tag */
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

    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $event->add(
            $this->render("tags-includes/module-configuration.html")
        );
    }

    public function onProductEditRightColumnBottom(HookRenderEvent $event)
    {
        $this->processFieldHook($event, 'product', $event->getArgument('product_id'));
    }

    public function onCategoryEditRightColumnBottom(HookRenderEvent $event)
    {
        $this->processFieldHook($event, 'category', $event->getArgument('category_id'));
    }

    public function onContentEditRightColumnBottom(HookRenderEvent $event)
    {
        $this->processFieldHook($event, 'content', $event->getArgument('content_id'));
    }

    public function onFolderEditRightColumnBottom(HookRenderEvent $event)
    {
        $this->processFieldHook($event, 'folder', $event->getArgument('folder_id'));
    }

    public function onBrandEditRightColumnBottom(HookRenderEvent $event)
    {
        $this->processFieldHook($event, 'brand', $event->getArgument('brand_id'));
    }

    public function addTagFieldJs(HookRenderEvent $event)
    {
        $imageJs = $this->addJS("tags-includes/assets/js/addFieldInForm.js", []);
        $event->add($imageJs);
    }

    public function hiddenTagTemplate(HookRenderEvent $event)
    {
        $event->add($this->render("tags-includes/tag-field.html"));
    }
}
