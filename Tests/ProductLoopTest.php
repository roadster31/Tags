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
 * Date: 04/08/2017 14:53
 */

namespace Tags\Tests;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Tags\EventListeners\EventManager;
use Thelia\Core\Template\Loop\Product;

/**
 * @property $container
 * @property $instance
 * @method assertEquals(int $int, $getCount)
 * @method assertNotEquals($productId, mixed $ID)
 */
class ProductLoopTest extends BaseTagTest
{
    public function getTestedClassName(): string
    {
        return 'Thelia\Core\Template\Loop\Product';
    }

    public function getTestedInstance(): Product
    {
        $this->container->setParameter(
            "thelia.parser.loops",
            [
                "product" => $this->getTestedClassName()
            ]
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new EventManager());

        $this->container->set("event_dispatcher", $eventDispatcher);

        return new Product($this->container);
    }

    public function getMandatoryArguments(): array
    {
        return [
            'tag' => '__test_tag1'
        ];
    }

    public function testSearchMode(): void
    {
        $this->instance->initializeArgs([
            'tag_match_mode' => 'partial',
            'tag' => '__test'
        ]);

        $dummy = null;

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(2, $loopResults->getCount());

        $this->instance->initializeArgs([
            'tag_match_mode' => 'partial',
            'tag' => 'tag1'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertEquals($this->productId, $substitutions['ID']);

        $this->instance->initializeArgs([
            'tag' => '__test_tag1'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertEquals($this->productId, $substitutions['ID']);

        $this->instance->initializeArgs([
            'tag_match_mode' => 'partial',
            'tag' => 'tag2'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertNotEquals($this->productId, $substitutions['ID']);

        $this->instance->initializeArgs([
            'tag' => '__test_tag1,__test_tag2'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(2, $loopResults->getCount());
    }
}
