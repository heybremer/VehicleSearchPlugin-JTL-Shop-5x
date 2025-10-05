<?php

declare(strict_types=1);

namespace Plugin\VehicleSearchPlugin;

use JTL\Cache\JTLCacheInterface;
use JTL\Plugin\PluginInterface;
use JTL\DB\DbInterface;
use PDO;
use PDOStatement;

/**
 * Vehicle Search Plugin data/service layer.
 */
class VehicleSearchPlugin
{
    public const PLUGIN_ID = 'VehicleSearchPlugin';

    public const VERSION = '1.0.0';

    private const CACHE_TAG = 'vehicle_search_plugin';

    private PluginInterface $plugin;

    private DbInterface $db;

    private JTLCacheInterface $cache;

    /**
     * @var array<string, mixed>
     */
    private array $configCache = [];

    public function __construct(PluginInterface $plugin, DbInterface $db, JTLCacheInterface $cache)
    {
        $this->plugin = $plugin;
        $this->db = $db;
        $this->cache = $cache;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getManufacturers(): array
    {
        $cacheKey = $this->cacheKey('manufacturers');
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $sql = <<<'SQL'
            SELECT h.kHersteller,
                   h.cName,
                   h.cBildpfad
              FROM thersteller h
              INNER JOIN tartikel a ON h.kHersteller = a.kHersteller
             WHERE h.nAktiv = 1
               AND a.nAktiv = 1
          GROUP BY h.kHersteller, h.cName, h.cBildpfad
          ORDER BY h.cName ASC
        SQL;

        $rows = $this->fetchAll($sql);
        $manufacturers = array_map(static function (array $row): array {
            return [
                'value' => (int) $row['kHersteller'],
                'text' => (string) $row['cName'],
                'image' => $row['cBildpfad'] ?? '',
            ];
        }, $rows);

        $this->cache->set($cacheKey, $manufacturers, [self::CACHE_TAG], $this->getCacheLifetime());

        return $manufacturers;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVehicleModelsByManufacturer(int $manufacturerId): array
    {
        $cacheKey = $this->cacheKey('models_' . $manufacturerId);
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $sql = <<<'SQL'
            SELECT DISTINCT mw.kMerkmalwert,
                            mw.cWert
              FROM tmerkmalwert mw
              INNER JOIN tartikelmerkmal am ON mw.kMerkmalwert = am.kMerkmalwert
              INNER JOIN tartikel a ON am.kArtikel = a.kArtikel
              INNER JOIN thersteller h ON a.kHersteller = h.kHersteller
             WHERE h.kHersteller = :manufacturerId
               AND mw.kMerkmal = 250
               AND mw.cWert IS NOT NULL
               AND mw.cWert != ''
               AND a.nAktiv = 1
          ORDER BY mw.cWert ASC
        SQL;

        $rows = $this->fetchAll($sql, ['manufacturerId' => $manufacturerId]);
        $models = array_map(static function (array $row): array {
            $value = (string) $row['cWert'];

            return [
                'value' => $value,
                'text' => $value,
            ];
        }, $rows);

        $this->cache->set($cacheKey, $models, [self::CACHE_TAG], $this->getCacheLifetime(1800));

        return $models;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVehicleTypesByModel(string $modelName): array
    {
        $modelName = trim($modelName);
        if ($modelName === '') {
            return [];
        }

        $cacheKey = $this->cacheKey('types_' . md5($modelName));
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $sql = <<<'SQL'
            SELECT DISTINCT mw.kMerkmalwert,
                            mw.cWert
              FROM tmerkmalwert mw
              INNER JOIN tartikelmerkmal am ON mw.kMerkmalwert = am.kMerkmalwert
              INNER JOIN tartikel a ON am.kArtikel = a.kArtikel
             WHERE mw.kMerkmal = 252
               AND mw.cWert LIKE :modelName
               AND mw.cWert IS NOT NULL
               AND mw.cWert != ''
               AND a.nAktiv = 1
          ORDER BY mw.cWert ASC
        SQL;

        $rows = $this->fetchAll($sql, ['modelName' => '%' . $modelName . '%']);
        $types = array_map(static function (array $row): array {
            $value = (string) $row['cWert'];

            return [
                'value' => $value,
                'text' => $value,
            ];
        }, $rows);

        $this->cache->set($cacheKey, $types, [self::CACHE_TAG], $this->getCacheLifetime(1800));

        return $types;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCategories(): array
    {
        $cacheKey = $this->cacheKey('categories_all');
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $sql = <<<'SQL'
            SELECT k.kKategorie,
                   k.cName,
                   k.cBeschreibung,
                   k.nSort,
                   k.kOberKategorie
              FROM tkategorie k
              INNER JOIN tkategorieartikel ka ON k.kKategorie = ka.kKategorie
              INNER JOIN tartikel a ON ka.kArtikel = a.kArtikel
             WHERE k.nAktiv = 1
               AND a.nAktiv = 1
          GROUP BY k.kKategorie, k.cName, k.cBeschreibung, k.nSort, k.kOberKategorie
          ORDER BY k.nSort ASC, k.cName ASC
        SQL;

        $rows = $this->fetchAll($sql);
        $categories = array_map(static function (array $row): array {
            return [
                'value' => (int) $row['kKategorie'],
                'text' => (string) $row['cName'],
                'description' => $row['cBeschreibung'] ?? '',
                'parent' => isset($row['kOberKategorie']) ? (int) $row['kOberKategorie'] : null,
                'sort' => isset($row['nSort']) ? (int) $row['nSort'] : 0,
            ];
        }, $rows);

        $this->cache->set($cacheKey, $categories, [self::CACHE_TAG], $this->getCacheLifetime());

        return $categories;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->configCache)) {
            $sql = 'SELECT cValue FROM tplugin_vehicle_search_config WHERE cName = :key LIMIT 1';
            $rows = $this->fetchAll($sql, ['key' => $key]);
            $this->configCache[$key] = $rows[0]['cValue'] ?? $default;
        }

        return $this->configCache[$key];
    }

    public function setConfig(string $key, $value): void
    {
        $sql = <<<'SQL'
            INSERT INTO tplugin_vehicle_search_config (cName, cValue)
                 VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE cValue = VALUES(cValue)
        SQL;

        $this->executeStatement($sql, ['key' => $key, 'value' => (string) $value]);
        $this->configCache[$key] = $value;
    }

    /**
     * @param array<string, mixed> $searchData
     */
    public function logSearchStats(array $searchData): void
    {
        $sql = <<<'SQL'
            INSERT INTO tplugin_vehicle_search_stats
                (cSearchType, cManufacturer, cModel, cVehicleType, kKategorie, nResults, cIP, cUserAgent)
            VALUES (:searchType, :manufacturer, :model, :vehicleType, :category, :results, :ip, :userAgent)
        SQL;

        $this->executeStatement($sql, [
            'searchType' => $searchData['searchType'] ?? '',
            'manufacturer' => $searchData['manufacturer'] ?? null,
            'model' => $searchData['model'] ?? null,
            'vehicleType' => $searchData['vehicleType'] ?? null,
            'category' => $searchData['category'] ?? null,
            'results' => (int) ($searchData['results'] ?? 0),
            'ip' => $this->getClientIp(),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSearchStats(int $limit = 100): array
    {
        $limit = max(1, $limit);
        $sql = <<<'SQL'
            SELECT cSearchType,
                   cManufacturer,
                   cModel,
                   cVehicleType,
                   nResults,
                   dSearched
              FROM tplugin_vehicle_search_stats
          ORDER BY dSearched DESC
             LIMIT :limit
        SQL;

        return $this->fetchAll($sql, ['limit' => ['value' => $limit, 'type' => PDO::PARAM_INT]]);
    }

    public function clearCache(): void
    {
        if (method_exists($this->cache, 'flushTags')) {
            $this->cache->flushTags([self::CACHE_TAG]);
        }

        $sql = 'DELETE FROM tplugin_vehicle_search_cache WHERE dExpires < NOW()';
        $this->executeStatement($sql);
    }

    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    private function cacheKey(string $suffix): string
    {
        return sprintf('%s_%s', self::PLUGIN_ID, $suffix);
    }

    private function getCacheLifetime(int $fallback = 3600): int
    {
        $configured = (int) $this->getConfig('cache_duration', $fallback);

        return $configured > 0 ? $configured : $fallback;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->prepare($sql, $params);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array<string, mixed> $params
     */
    private function executeStatement(string $sql, array $params = []): void
    {
        $statement = $this->prepare($sql, $params);
        $statement->execute();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function prepare(string $sql, array $params): PDOStatement
    {
        $statement = $this->db->getPDO()->prepare($sql);
        foreach ($params as $key => $value) {
            $normalizedKey = (string) $key;
            $parameter = $normalizedKey !== '' && $normalizedKey[0] === ':'
                ? $normalizedKey
                : ':' . $normalizedKey;
            $type = PDO::PARAM_STR;
            $finalValue = $value;

            if (is_array($value) && array_key_exists('value', $value)) {
                $finalValue = $value['value'];
                $type = $value['type'] ?? (is_int($finalValue) ? PDO::PARAM_INT : PDO::PARAM_STR);
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif ($value === null) {
                $type = PDO::PARAM_NULL;
            }

            $statement->bindValue($parameter, $finalValue, $type);
        }

        return $statement;
    }

    private function getClientIp(): string
    {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            $value = $_SERVER[$key] ?? '';
            if (is_string($value) && $value !== '') {
                $ip = explode(',', $value)[0];

                return trim($ip);
            }
        }

        return '';
    }
}

