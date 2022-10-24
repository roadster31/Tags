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
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Tags\Model\TagsQuery;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class Tags extends BaseModule
{
    /** @var string */
    public const DOMAIN_NAME = 'tags';

    public const DELETE_ORPHAN_EVENT = 'tags.delete_orphan_event';

    public function postActivation(ConnectionInterface $con = null): void
    {
        try {
            TagsQuery::create()->findOne();
        } catch (\Exception $ex) {
            $database = new Database($con->getWrappedConnection());
            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));
        }
    }

    public function update($currentVersion, $newVersion, ?ConnectionInterface $con = null): void
    {
        $database = new Database($con->getWrappedConnection());

        if (version_compare($currentVersion, '1.2.0') === -1) {
            $database->insertSql(null, array(__DIR__ . '/Config/update1.1.sql'));
        }

        // 1.2.1 database update
        if (version_compare($newVersion, '1.2.1') === 0) {
            $database->insertSql(null, array(__DIR__ . '/Config/update1.2.1.sql'));
        }
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR.ucfirst(self::getModuleCode()).'/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
