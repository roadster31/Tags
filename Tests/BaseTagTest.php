<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

/**
 * Created by Franck Allimant, CQFDev <franck@cqfdev.fr>
 * Date: 04/08/2017 16:32
 */

namespace Tags\Tests;

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tags\EventListeners\EventManager;
use Tags\Model\TagsQuery;
use Thelia\Model\ProductQuery;
use Thelia\Tests\Core\Template\Element\BaseLoopTestor;

abstract class BaseTagTest extends BaseLoopTestor
{
    protected $productId;

    public function setUp()
    {
        $prod = ProductQuery::create()->findOne();

        $this->productId = $prod->getId();

        $tag = new \Tags\Model\Tags();
        $tag->setSource('product')
            ->setSourceId($prod->getId())
            ->setTag('__test_tag1')
            ->save();

        $prod2 = ProductQuery::create()->filterById($prod->getId(), Criteria::NOT_EQUAL)->findOne();

        $tag = new \Tags\Model\Tags();
        $tag->setSource('product')
            ->setSourceId($prod2->getId())
            ->setTag('__test_tag2')
            ->save();

        parent::setUp();
    }

    public function tearDown()
    {
        TagsQuery::create()
            ->filterByTag('__test_%', Criteria::LIKE)
            ->delete();

        parent::tearDown();
    }
}
