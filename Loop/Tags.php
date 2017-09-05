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
 * @method string getTagMatchMode()
 */
class Tags extends BaseLoop implements PropelSearchLoopInterface
{
    protected $timestampable = true;

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            new Argument(
                'source',
                new TypeCollection(
                    new EnumType([ 'product', 'category', 'content', 'folder', 'brand' ])
                )
            ),
            new Argument(
                'exclude_source',
                new TypeCollection(
                    new EnumType([ 'product', 'category', 'content', 'folder', 'brand' ])
                )
            ),
            Argument::createIntListTypeArgument('source_id'),
            Argument::createIntListTypeArgument('exclude_source_id'),
            Argument::createAnyListTypeArgument('tag'),
            Argument::createAnyListTypeArgument('exclude_tag'),
            new Argument(
                'tag_match_mode',
                new TypeCollection(new EnumType([ 'exact', 'partial' ])),
                'exact'
            ),
            new Argument(
                'order',
                new TypeCollection(
                    new EnumListType(
                        [
                            'id', 'id-reverse',
                            'alpha', 'alpha-reverse',
                            'source', 'source-reverse',
                            'source-id', 'source-id-reverse',
                            'create-date', 'create-date-reverse',
                            'update-date', 'update-date-reverse',
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

        if (null !== $tags = $this->getTag()) {
            if ('exact' === $this->getTagMatchMode()) {
                $query->filterByTag($tags, Criteria::IN);
            } else {
                foreach ($tags as $tag) {
                    $query->filterByTag("%$tag%", Criteria::LIKE);
                }
            }
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

                case 'source-id':
                    $query->orderBySourceId();
                    break;
                case 'source-id-reverse':
                    $query->orderBySourceId(Criteria::DESC);
                    break;

                case 'create-date':
                    $query->orderByCreatedAt();
                    break;
                case 'create-date-reverse':
                    $query->orderByCreatedAt(Criteria::DESC);
                    break;

                case 'update-date':
                    $query->orderByUpdatedAt();
                    break;
                case 'update-date-reverse':
                    $query->orderByUpdatedAt(Criteria::DESC);
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
