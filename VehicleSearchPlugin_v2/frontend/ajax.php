<?php

namespace Plugin\VehicleSearchPlugin\Frontend;

use JTL\Plugin\PluginInterface;
use JTL\DB\DbInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\Shop;
use Exception;

/**
 * Vehicle Search Plugin AJAX Endpoint for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\Frontend
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */

// Get plugin instance
$plugin = Shop::Container()->getPluginLoader()->getPluginById('VehicleSearchPlugin');
if (!$plugin) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Plugin not found']);
    exit;
}

// Get dependencies
$db = Shop::Container()->getDB();
$cache = Shop::Container()->getCache();

// Initialize plugin service
$vehicleSearchService = new \Plugin\VehicleSearchPlugin\Src\VehicleSearchPlugin($plugin, $db, $cache);

// Set JSON header
header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Check CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Get action
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'getManufacturers':
            $manufacturers = $vehicleSearchService->getManufacturers();
            echo json_encode(['success' => true, 'manufacturers' => $manufacturers]);
            break;
            
        case 'getVehicleModels':
            $manufacturerId = (int)($_POST['manufacturer_id'] ?? 0);
            if ($manufacturerId <= 0) {
                throw new Exception('Invalid manufacturer ID');
            }
            
            $models = $vehicleSearchService->getVehicleModelsByManufacturer($manufacturerId);
            echo json_encode(['success' => true, 'models' => $models]);
            break;
            
        case 'getVehicleTypes':
            $modelName = trim($_POST['model_name'] ?? '');
            if (empty($modelName)) {
                throw new Exception('Invalid model name');
            }
            
            $types = $vehicleSearchService->getVehicleTypesByModel($modelName);
            echo json_encode(['success' => true, 'types' => $types]);
            break;
            
        case 'getCategories':
            $categories = $vehicleSearchService->getCategories();
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;
            
        case 'logSearch':
            $searchData = [
                'searchType' => $_POST['search_type'] ?? '',
                'manufacturer' => $_POST['manufacturer'] ?? null,
                'model' => $_POST['model'] ?? null,
                'vehicleType' => $_POST['vehicle_type'] ?? null,
                'category' => $_POST['category'] ?? null,
                'results' => (int)($_POST['results'] ?? 0)
            ];
            
            $vehicleSearchService->logSearchStats($searchData);
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}