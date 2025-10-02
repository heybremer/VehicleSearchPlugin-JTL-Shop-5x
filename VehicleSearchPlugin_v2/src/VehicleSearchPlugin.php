<?php

namespace Plugin\VehicleSearchPlugin\Src;

use JTL\Plugin\PluginInterface;
use JTL\DB\DbInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\Smarty\JTLSmarty;
use Exception;

/**
 * Vehicle Search Plugin for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\Src
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */
class VehicleSearchPlugin
{
    /**
     * Plugin ID
     */
    const PLUGIN_ID = 'VehicleSearchPlugin';
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * @var PluginInterface
     */
    private $plugin;
    
    /**
     * @var DbInterface
     */
    private $db;
    
    /**
     * @var JTLCacheInterface
     */
    private $cache;
    
    /**
     * Configuration cache
     */
    private static $configCache = [];
    
    /**
     * Constructor
     * 
     * @param PluginInterface $plugin
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(PluginInterface $plugin, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->plugin = $plugin;
        $this->db = $db;
        $this->cache = $cache;
    }
    
    /**
     * Get manufacturers from database
     * 
     * @return array
     */
    public function getManufacturers(): array
    {
        $cacheKey = 'manufacturers_' . md5('all');
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        
        $sql = "SELECT h.kHersteller, h.cName, h.cBildpfad
                FROM thersteller h
                INNER JOIN tartikel a ON h.kHersteller = a.kHersteller
                WHERE h.nAktiv = 1 
                AND a.nAktiv = 1
                GROUP BY h.kHersteller, h.cName, h.cBildpfad
                ORDER BY h.cName ASC";
        
        $result = $this->db->executeQuery($sql);
        $manufacturers = [];
        
        while ($row = $result->fetch()) {
            $manufacturers[] = [
                'value' => $row['kHersteller'],
                'text' => $row['cName'],
                'image' => $row['cBildpfad']
            ];
        }
        
        $this->cache->set($cacheKey, $manufacturers, 3600);
        return $manufacturers;
    }
    
    /**
     * Get vehicle models by manufacturer
     * 
     * @param int $manufacturerId
     * @return array
     */
    public function getVehicleModelsByManufacturer(int $manufacturerId): array
    {
        $cacheKey = 'models_' . $manufacturerId;
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        
        $sql = "SELECT DISTINCT mw.kMerkmalwert, mw.cWert
                FROM tmerkmalwert mw
                INNER JOIN tartikelmerkmal am ON mw.kMerkmalwert = am.kMerkmalwert
                INNER JOIN tartikel a ON am.kArtikel = a.kArtikel
                INNER JOIN thersteller h ON a.kHersteller = h.kHersteller
                WHERE h.kHersteller = :manufacturerId 
                AND mw.kMerkmal = 250
                AND mw.cWert IS NOT NULL 
                AND mw.cWert != '' 
                AND a.nAktiv = 1
                ORDER BY mw.cWert ASC";
        
        $result = $this->db->executeQuery($sql, ['manufacturerId' => $manufacturerId]);
        $models = [];
        
        while ($row = $result->fetch()) {
            $models[] = [
                'value' => $row['cWert'],
                'text' => $row['cWert']
            ];
        }
        
        $this->cache->set($cacheKey, $models, 1800);
        return $models;
    }
    
    /**
     * Get vehicle types by model
     * 
     * @param string $modelName
     * @return array
     */
    public function getVehicleTypesByModel(string $modelName): array
    {
        $cacheKey = 'types_' . md5($modelName);
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        
        $sql = "SELECT DISTINCT mw.kMerkmalwert, mw.cWert
                FROM tmerkmalwert mw
                INNER JOIN tartikelmerkmal am ON mw.kMerkmalwert = am.kMerkmalwert
                INNER JOIN tartikel a ON am.kArtikel = a.kArtikel
                WHERE mw.kMerkmal = 252
                AND mw.cWert LIKE :modelName
                AND mw.cWert IS NOT NULL 
                AND mw.cWert != '' 
                AND a.nAktiv = 1
                ORDER BY mw.cWert ASC";
        
        $result = $this->db->executeQuery($sql, ['modelName' => '%' . $modelName . '%']);
        $types = [];
        
        while ($row = $result->fetch()) {
            $types[] = [
                'value' => $row['cWert'],
                'text' => $row['cWert']
            ];
        }
        
        $this->cache->set($cacheKey, $types, 1800);
        return $types;
    }
    
    /**
     * Get categories
     * 
     * @return array
     */
    public function getCategories(): array
    {
        $cacheKey = 'categories_all';
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        
        $sql = "SELECT k.kKategorie, k.cName, k.cBeschreibung, k.nSort, k.kOberKategorie
                FROM tkategorie k
                INNER JOIN tkategorieartikel ka ON k.kKategorie = ka.kKategorie
                INNER JOIN tartikel a ON ka.kArtikel = a.kArtikel
                WHERE k.nAktiv = 1 
                AND a.nAktiv = 1
                GROUP BY k.kKategorie, k.cName, k.cBeschreibung, k.nSort, k.kOberKategorie
                ORDER BY k.nSort ASC, k.cName ASC";
        
        $result = $this->db->executeQuery($sql);
        $categories = [];
        
        while ($row = $result->fetch()) {
            $categories[] = [
                'value' => $row['kKategorie'],
                'text' => $row['cName'],
                'description' => $row['cBeschreibung'],
                'parent' => $row['kOberKategorie'],
                'sort' => $row['nSort']
            ];
        }
        
        $this->cache->set($cacheKey, $categories, 3600);
        return $categories;
    }
    
    /**
     * Get plugin configuration
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        if (!isset(self::$configCache[$key])) {
            $sql = "SELECT cValue FROM tplugin_vehicle_search_config WHERE cName = :key";
            $result = $this->db->executeQuery($sql, ['key' => $key]);
            $row = $result->fetch();
            self::$configCache[$key] = $row ? $row['cValue'] : $default;
        }
        
        return self::$configCache[$key];
    }
    
    /**
     * Set plugin configuration
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfig(string $key, $value): void
    {
        $sql = "INSERT INTO tplugin_vehicle_search_config (cName, cValue) 
                VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE cValue = :value";
        
        $this->db->executeQuery($sql, ['key' => $key, 'value' => $value]);
        self::$configCache[$key] = $value;
    }
    
    /**
     * Log search statistics
     * 
     * @param array $searchData
     * @return void
     */
    public function logSearchStats(array $searchData): void
    {
        $sql = "INSERT INTO tplugin_vehicle_search_stats 
                (cSearchType, cManufacturer, cModel, cVehicleType, kKategorie, nResults, cIP, cUserAgent) 
                VALUES (:searchType, :manufacturer, :model, :vehicleType, :category, :results, :ip, :userAgent)";
        
        $this->db->executeQuery($sql, [
            'searchType' => $searchData['searchType'] ?? '',
            'manufacturer' => $searchData['manufacturer'] ?? null,
            'model' => $searchData['model'] ?? null,
            'vehicleType' => $searchData['vehicleType'] ?? null,
            'category' => $searchData['category'] ?? null,
            'results' => $searchData['results'] ?? 0,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    /**
     * Get search statistics
     * 
     * @param int $limit
     * @return array
     */
    public function getSearchStats(int $limit = 100): array
    {
        $sql = "SELECT cSearchType, cManufacturer, cModel, cVehicleType, nResults, dSearched
                FROM tplugin_vehicle_search_stats 
                ORDER BY dSearched DESC 
                LIMIT :limit";
        
        $result = $this->db->executeQuery($sql, ['limit' => $limit]);
        $stats = [];
        
        while ($row = $result->fetch()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
    
    /**
     * Clear cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache->flush();
        
        // Clear plugin-specific cache
        $sql = "DELETE FROM tplugin_vehicle_search_cache WHERE dExpires < NOW()";
        $this->db->executeQuery($sql);
    }
    
    /**
     * Get plugin instance
     * 
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }
}