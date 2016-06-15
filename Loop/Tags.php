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

namespace Tags\Loop;

use Propel\Runtime\ActiveQuery\Criteria;
use Tags\Model\TagsQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Type\EnumListType;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

/**
 * Tags loop
 *
 * @author  Franck Allimant <franck@cqfdev.fr>
 *
 * {@inheritdoc}
 * @method int[] getId()
 * @method int[] getSourceId()
 * @method int[] getExcludeSourceId()
 * @method string getSource()
 * @method string getExcludeSource()
 * @method string[] getTag()
 * @method string[] getExcludeTag()
 * @method string[] getOrder()
 */
class Tags extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            new Argument(
                'source',
                new TypeCollection(
                    new EnumType([ 'product', 'category', 'content', 'folder' ])
                )
            ),
            new Argument(
                'exclude_source',
                new TypeCollection(
                    new EnumType([ 'product', 'category', 'content', 'folder' ])
                )
            ),
            Argument::createIntListTypeArgument('source_id'),
            Argument::createIntListTypeArgument('exclude_source_id'),
            Argument::createAnyListTypeArgument('tag'),
            Argument::createAnyListTypeArgument('exclude_tag'),
            new Argument(
                'order',
                new TypeCollection(
                    new EnumListType(
                        [
                            'id', 'id-reverse',
                            'alpha', 'alpha-reverse',
                            'source', 'source-reverse',
                            'source-id', 'source-id-reverse',
                            'random'
                        ]
                    )
                ),
                'alpha'
            )
        );
    }

    public function buildModelCriteria()
    {
        $query = TagsQuery::create();

        if (null !== $id = $this->getId()) {
            $query->filterBySource($id, Criteria::IN);
        }

        if (null !== $source = $this->getSource()) {
            $query->filterBySource($source);
        }

        if (null !== $excludeSource = $this->getExcludeSource()) {
            $query->filterBySource($excludeSource, Criteria::NOT_IN);
        }

        if (null !== $sourceId = $this->getSourceId()) {
            $query->filterBySourceId($sourceId, Criteria::IN);
        }

        if (null !== $excludeSourceId = $this->getExcludeSourceId()) {
            $query->filterBySourceId($excludeSourceId, Criteria::NOT_IN);
        }

        if (null !== $excludeTag = $this->getExcludeTag()) {
            $query->filterByTag($excludeTag, Criteria::NOT_IN);
        }

        if (null !== $tag = $this->getTag()) {
            $query->filterByTag($tag, Criteria::IN);
        }

        $orderList = $this->getOrder();

        foreach ($orderList as $order) {
            switch ($order) {
                case 'alpha':
                    $query->orderByTag();
                    break;
                case 'alpha-reverse':
                    $query->orderByTag(Criteria::DESC);
                    break;

                case 'id':
                    $query->orderById();
                    break;
                case 'id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;

                case 'source':
                    $query->orderBySource();
                    break;
                case 'source-reverse':
                    $query->orderBySource(Criteria::DESC);
                    break;

                case 'source_id':
                    $query->orderBySourceId();
                    break;
                case 'source-id-reverse':
                    $query->orderBySourceId(Criteria::DESC);
                    break;

                case 'random':
                    $query->clearOrderByColumns();
                    $query->addAscendingOrderByColumn('RAND()');
                    break;
            }
        }

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var \Tags\Model\Tags $tag */
        foreach ($loopResult->getResultDataCollection() as $tag) {
            $loopResultRow = new LoopResultRow($tag);

            $loopResultRow
                ->set("ID", $tag->getId())
                ->set("SOURCE", $tag->getSource())
                ->set("SOURCE_ID", $tag->getSourceId())
                ->set("TAG", $tag->getTag())
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
