<?php
/*************************************************************************************/
/*      Copyright (c) OpenStudio                                                     */
/*      web : https://www.openstudio.fr                                              */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

/**
 * Created by Franck Allimant, OpenStudio <fallimant@openstudio.fr>
 * Projet: Thelia 2
 * Date: 04/09/2020
 */

namespace Tags\Smarty;

use Propel\Runtime\ActiveQuery\Criteria;
use Smarty_Internal_Template;
use Tags\Model\TagsQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class TagsPlugin extends AbstractSmartyPlugin
{

    public function hasTag($params, Smarty_Internal_Template $template): bool
    {
        $id = (int) $params['id'];
        $source = $params['source'];
        $tagList = $params['tag'];

        $tagArray = explode(',', $tagList);

        array_walk($tagArray, function (&$value, $key) {
            $value = trim($value);
        });

        return TagsQuery::create()
                ->filterBySourceId($id)
                ->filterBySource($source)
                ->filterByTag($tagArray, Criteria::IN)
                ->count() > 0
            ;
    }

    /**
     * Define the various smarty plugins hendled by this class
     *
     * @return array an array of smarty plugin descriptors
     */
    public function getPluginDescriptors(): array
    {
        return array(
            new SmartyPluginDescriptor('function', 'has_tag', $this, 'hasTag')
        );
    }
}
