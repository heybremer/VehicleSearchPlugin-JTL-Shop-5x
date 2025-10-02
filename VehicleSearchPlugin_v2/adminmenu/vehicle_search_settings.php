<?php

namespace Plugin\VehicleSearchPlugin\AdminMenu;

use JTL\Plugin\PluginInterface;
use JTL\DB\DbInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Exception;

/**
 * Vehicle Search Plugin Admin Settings for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\AdminMenu
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */

// Get plugin instance
$plugin = Shop::Container()->getPluginLoader()->getPluginById('VehicleSearchPlugin');
if (!$plugin) {
    die('Plugin not found');
}

// Get dependencies
$db = Shop::Container()->getDB();
$cache = Shop::Container()->getCache();

// Initialize plugin service
$vehicleSearchService = new \Plugin\VehicleSearchPlugin\Src\VehicleSearchPlugin($plugin, $db, $cache);

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    try {
        $vehicleSearchService->setConfig('enable_ajax', $_POST['enable_ajax'] ?? '0');
        $vehicleSearchService->setConfig('default_search_type', $_POST['default_search_type'] ?? 'M');
        $vehicleSearchService->setConfig('max_results_per_page', (int)($_POST['max_results_per_page'] ?? 20));
        $vehicleSearchService->setConfig('enable_manufacturer_filter', $_POST['enable_manufacturer_filter'] ?? '0');
        $vehicleSearchService->setConfig('enable_model_filter', $_POST['enable_model_filter'] ?? '0');
        $vehicleSearchService->setConfig('enable_type_filter', $_POST['enable_type_filter'] ?? '0');
        $vehicleSearchService->setConfig('enable_category_filter', $_POST['enable_category_filter'] ?? '0');
        $vehicleSearchService->setConfig('cache_duration', (int)($_POST['cache_duration'] ?? 3600));
        $vehicleSearchService->setConfig('show_vehicle_images', $_POST['show_vehicle_images'] ?? '0');
        $vehicleSearchService->setConfig('enable_advanced_search', $_POST['enable_advanced_search'] ?? '0');
        
        $success = 'Settings saved successfully!';
    } catch (Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}

// Handle cache clear
if (isset($_POST['clear_cache'])) {
    try {
        $vehicleSearchService->clearCache();
        $success = 'Cache cleared successfully!';
    } catch (Exception $e) {
        $error = 'Error clearing cache: ' . $e->getMessage();
    }
}

// Get current configuration
$config = [
    'enable_ajax' => $vehicleSearchService->getConfig('enable_ajax', '1'),
    'default_search_type' => $vehicleSearchService->getConfig('default_search_type', 'M'),
    'max_results_per_page' => $vehicleSearchService->getConfig('max_results_per_page', '20'),
    'enable_manufacturer_filter' => $vehicleSearchService->getConfig('enable_manufacturer_filter', '1'),
    'enable_model_filter' => $vehicleSearchService->getConfig('enable_model_filter', '1'),
    'enable_type_filter' => $vehicleSearchService->getConfig('enable_type_filter', '1'),
    'enable_category_filter' => $vehicleSearchService->getConfig('enable_category_filter', '1'),
    'cache_duration' => $vehicleSearchService->getConfig('cache_duration', '3600'),
    'show_vehicle_images' => $vehicleSearchService->getConfig('show_vehicle_images', '1'),
    'enable_advanced_search' => $vehicleSearchService->getConfig('enable_advanced_search', '1')
];

// Get search statistics
$searchStats = $vehicleSearchService->getSearchStats(50);

$smarty = Shop::Smarty();
$smarty->assign('config', $config);
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('searchStats', $searchStats);
$smarty->assign('pluginUrl', $plugin->getPaths()->getFrontendPath());

echo $smarty->fetch($plugin->getPaths()->getAdminPath() . 'vehicle_search_settings.tpl');