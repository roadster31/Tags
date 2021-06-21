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
 * Date: 16/06/2021
 */

namespace Tags\Events;

use Tags\Model\Tags;
use Thelia\Core\Event\ActionEvent;

/**
 * This event if dispatched when a tag for a non standard object shoud be deleted.
 *
 * Class DeleteOrphanEvent
 * @package Tags\Events
 */
class DeleteOrphanEvent extends ActionEvent
{
    /** @var Tags */
    protected $tag;

    /**
     * DeleteOrphanEvent constructor.
     * @param Tags $tag
     */
    public function __construct(Tags $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return Tags
     */
    public function getTag(): Tags
    {
        return $this->tag;
    }
}
