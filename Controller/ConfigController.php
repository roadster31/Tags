<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Tags\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $method = new \ReflectionMethod($queryClass, 'create');
            $search = $method->invoke(null); // Static !

            if (null == $search->findPk($tag->getSourceId())) {
                $tag->delete();
            }
        }

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl('/admin/modules/Tags')
        );
    }
}
