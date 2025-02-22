<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Behat\Service;

use CoreShop\Bundle\CoreBundle\Installer;
use Doctrine\DBAL\DriverManager;

class Setup
{
    private static bool $pimcoreSetupDone = false;

    private static bool $coreShopSetupDone = false;

    public static function setupPimcore(): void
    {
        if (getenv('CORESHOP_SKIP_DB_SETUP')) {
            return;
        }

        if (static::$pimcoreSetupDone) {
            return;
        }

        $connection = \Pimcore::getContainer()->get('database_connection');

        $dbName = $connection->getParams()['dbname'];
        $params = $connection->getParams();
        $config = $connection->getConfiguration();

        unset($params['url'], $params['dbname']);

        // use a dedicated setup connection as the framework connection is bound to the DB and will
        // fail if the DB doesn't exist
        $setupConnection = DriverManager::getConnection($params, $config);
        $schemaManager = $setupConnection->getSchemaManager();

        $databases = $schemaManager->listDatabases();
        if (in_array($dbName, $databases)) {
            $schemaManager->dropDatabase($connection->quoteIdentifier($dbName));
        }

        $schemaManager->createDatabase($connection->quoteIdentifier($dbName));

        if (!$connection->isConnected()) {
            $connection->connect();
        }

        $installer = new \Pimcore\Bundle\InstallBundle\Installer(
            \Pimcore::getContainer()->get('monolog.logger.pimcore'),
            \Pimcore::getContainer()->get('event_dispatcher'),
        );

        $installer->setupDatabase([
            'username' => 'admin',
            'password' => 'coreshop',
        ]);

        static::$pimcoreSetupDone = true;
    }

    public static function setupDone(): bool
    {
        return getenv('CORESHOP_SKIP_DB_SETUP') || (static::$pimcoreSetupDone && static::$coreShopSetupDone);
    }

    public static function setupCoreShop(): void
    {
        if (getenv('CORESHOP_SKIP_DB_SETUP')) {
            return;
        }

        if (static::$coreShopSetupDone) {
            return;
        }

        $installer = \Pimcore::getContainer()->get(Installer::class);
        $installer->install();

        static::$coreShopSetupDone = true;
    }
}
