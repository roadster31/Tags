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

namespace Tags\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Tags\Events\DeleteOrphanEvent;
use Tags\Model\Tags;
use Tags\Model\TagsQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

class ConfigController extends BaseAdminController
{
    public function fixOrphans()
    {
        if (null !== $response = $this->checkAuth('Tags', [], [AccessManager::DELETE])) {
            return $response;
        }

        $tagList = TagsQuery::create()->find();

        /** @var Tags $tag */
        foreach ($tagList as $tag) {
            $queryClass = "Thelia\\Model\\" . ucfirst($tag->getSource()) . 'Query';
            try {
                $method = new \ReflectionMethod($queryClass, 'create');
                $search = $method->invoke(null); // Static !

                if (null == $search->findPk($tag->getSourceId())) {
                    $tag->delete();
                }
            } catch (\ReflectionException $ex) {
                // Method does not exists => fire an event to whom may process it
                $this->getDispatcher()->dispatch(\Tags\Tags::DELETE_ORPHAN_EVENT, new DeleteOrphanEvent($tag));
            }
        }

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl('/admin/module/Tags')
        );
    }
}
