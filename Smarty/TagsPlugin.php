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
use Tags\Model\TagsQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class TagsPlugin extends AbstractSmartyPlugin
{

    public function hasTag($params, \Smarty_Internal_Template $template)
    {
        $id = (int) $params['id'];
        $source = $params['source'];
        $tagList = $params['tag'];
        $tagMatchMode = $params['tag_match_mode'] ? $params['tag_match_mode'] : 'exact';

        $tagArray = explode(',', $tagList);

        array_walk($tagArray, function (&$value, $key) {
            $value = trim($value);
        });

        $query = TagsQuery::create()
                ->filterBySourceId($id)
                ->filterBySource($source);

        if('exact' === $tagMatchMode) {
            $query->filterByTag($tagArray, Criteria::IN);
        } else {
            foreach ($tagArray as $tag) {
                $query->filterByTag("%$tag%", Criteria::LIKE);
            }
        }

        return $query->count() > 0;
    }

    /**
     * Define the various smarty plugins hendled by this class
     *
     * @return array an array of smarty plugin descriptors
     */
    public function getPluginDescriptors()
    {
        return array(
            new SmartyPluginDescriptor('function', 'has_tag', $this, 'hasTag')
        );
    }
}
