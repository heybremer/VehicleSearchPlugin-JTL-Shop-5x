<?php

namespace Plugin\VehicleSearchPlugin\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class DropVehicleSearchTables
 * @package Plugin\VehicleSearchPlugin\Migrations
 */
class DropVehicleSearchTables extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute("DROP TABLE IF EXISTS `tplugin_vehicle_search_stats`");
        $this->execute("DROP TABLE IF EXISTS `tplugin_vehicle_search_cache`");
        $this->execute("DROP TABLE IF EXISTS `tplugin_vehicle_search_config`");
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        // Recreate tables if needed for rollback
        $this->execute("
            CREATE TABLE IF NOT EXISTS `tplugin_vehicle_search_config` (
                `kPluginVehicleSearchConfig` int(11) NOT NULL AUTO_INCREMENT,
                `cName` varchar(255) NOT NULL,
                `cValue` text,
                `cDescription` varchar(500) DEFAULT NULL,
                `dCreated` datetime DEFAULT CURRENT_TIMESTAMP,
                `dModified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`kPluginVehicleSearchConfig`),
                UNIQUE KEY `cName` (`cName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `tplugin_vehicle_search_cache` (
                `kPluginVehicleSearchCache` int(11) NOT NULL AUTO_INCREMENT,
                `cCacheKey` varchar(255) NOT NULL,
                `cCacheData` longtext,
                `dExpires` datetime NOT NULL,
                `dCreated` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`kPluginVehicleSearchCache`),
                UNIQUE KEY `cCacheKey` (`cCacheKey`),
                KEY `dExpires` (`dExpires`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `tplugin_vehicle_search_stats` (
                `kPluginVehicleSearchStats` int(11) NOT NULL AUTO_INCREMENT,
                `cSearchType` varchar(10) NOT NULL,
                `cManufacturer` varchar(255) DEFAULT NULL,
                `cModel` varchar(255) DEFAULT NULL,
                `cVehicleType` varchar(255) DEFAULT NULL,
                `kKategorie` int(11) DEFAULT NULL,
                `nResults` int(11) DEFAULT 0,
                `dSearched` datetime DEFAULT CURRENT_TIMESTAMP,
                `cIP` varchar(45) DEFAULT NULL,
                `cUserAgent` text,
                PRIMARY KEY (`kPluginVehicleSearchStats`),
                KEY `dSearched` (`dSearched`),
                KEY `cSearchType` (`cSearchType`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
