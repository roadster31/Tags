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
use Tags\Api\Metadata\TagsResourceMetadataCollectionFactory;
use Thelia\Api\Resource\Category;
use Thelia\Api\Resource\Content;
use Thelia\Api\Resource\Folder;
use Thelia\Api\Resource\Product;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Thelia\Core\Install\Database;
use Thelia\Module\BaseModule;

class Tags extends BaseModule
{
    /** @var string */
    public const DOMAIN_NAME = 'tags';

    public const DELETE_ORPHAN_EVENT = 'tags.delete_orphan_event';

    /** @var string[] */
    public const array SUPPORTED_RESOURCES_SOURCES = [
        Product::class => 'product',
        Category::class => 'category',
        Content::class => 'content',
        Folder::class => 'folder',
    ];

    /** @var string[] */
    public const array SUPPORTED_RESOURCES_URI = [
        Product::class => '/front/products',
        Category::class => '/front/categories',
        Content::class => '/front/contents',
        Folder::class => '/front/folders',
    ];

    public function postActivation(ConnectionInterface $con = null): void
    {
        if (! self::getConfigValue('is_initialised')) {
            $database = new Database($con->getWrappedConnection());
            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

            self::getConfigValue('is_initialised', true);
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
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
            ->exclude(["/I18n/*"])
            ->autowire()
            ->autoconfigure();

        $servicesConfigurator
            ->set(TagsResourceMetadataCollectionFactory::class)
            ->decorate('api_platform.metadata.resource.metadata_collection_factory', null, -20)
            ->args([
                service('.inner'),
            ]);
    }
}
