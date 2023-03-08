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

use Tags\Loop\Tags;

/**
 * @property $container
 * @property $instance
 * @method assertEquals(int $int, $getCount)
 * @method assertNotEquals($productId, mixed $SOURCE_ID)
 */
class TagLoopTest extends BaseTagTest
{
    public function getTestedClassName(): string
    {
        return 'Tags\Loop\Tags';
    }

    public function getTestedInstance(): Tags
    {
        return new Tags($this->container);
    }

    public function getMandatoryArguments(): array
    {
        return [
            'source' => 'product'
        ];
    }

    public function testSearchMode(): void
    {
        $this->instance->initializeArgs([
            'source' => 'product',
            'tag_match_mode' => 'partial',
            'tag' => '__test'
        ]);

        $dummy = null;

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(2, $loopResults->getCount());

        $this->instance->initializeArgs([
            'source' => 'product',
            'tag_match_mode' => 'partial',
            'tag' => 'tag1'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertEquals($this->productId, $substitutions['SOURCE_ID']);

        $this->instance->initializeArgs([
            'source' => 'product',
            'tag_match_mode' => 'exact',
            'tag' => '__test_tag1'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertEquals($this->productId, $substitutions['SOURCE_ID']);

        $this->instance->initializeArgs([
            'source' => 'product',
            'tag_match_mode' => 'partial',
            'tag' => 'tag2'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertNotEquals($this->productId, $substitutions['SOURCE_ID']);

        $this->instance->initializeArgs([
            'source' => 'product',
            'exclude_tag' => '__test_tag1'
        ]);

        $loopResults = $this->instance->exec($dummy);
        $this->assertEquals(1, $loopResults->getCount());

        $substitutions = $loopResults->current()->getVarVal();
        $this->assertNotEquals($this->productId, $substitutions['SOURCE_ID']);

    }
}
