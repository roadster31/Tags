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
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class HookManager extends BaseHook
{
    private function processFieldHook(HookRenderEvent $event, $sourceType, $sourceId)
    {
        $tags = TagsQuery::create()
            ->filterBySource($sourceType)
            ->filterBySourceId($sourceId)
            ->find();

        $tagValue = '';

        /** @var Tags $tag */
        foreach($tags as $tag) {
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
}
