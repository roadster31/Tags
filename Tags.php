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

namespace Tags;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class Tags extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'tags';

    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con->getWrappedConnection());
        $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));
    }


    public function update($currentVersion, $newVersion, ConnectionInterface $con = null)
    {
        if (version_compare($currentVersion, '1.2.0') == -1) {
            $database = new Database($con->getWrappedConnection());
            $database->insertSql(null, array(__DIR__ . '/Config/update1.1.sql'));
        }
    }
}
