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

namespace CoreShop\Component\Pimcore\Db;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

final class Db extends \Pimcore\Db
{
    public static function getDoctrineConnection(): Connection
    {
        /**
         * @var Connection $connection
         */
        $connection = self::getConnection();

        Assert::isInstanceOf($connection, Connection::class);

        return $connection;
    }

    /**
     * @param string $table
     */
    public static function getColumns($table): array
    {
        $db = self::getDoctrineConnection();

        $data = $db->fetchAllAssociative('SHOW COLUMNS FROM ' . $table);
        $columns = [];

        foreach ($data as $d) {
            $columns[] = $d['Field'];
        }

        return $columns;
    }

    /**
     * Check if table exists.
     *
     * @param string $table
     */
    public static function tableExists($table): bool
    {
        $db = self::getDoctrineConnection();

        $result = $db->fetchAllAssociative("SHOW TABLES LIKE '$table'");

        return count($result) > 0;
    }
}
