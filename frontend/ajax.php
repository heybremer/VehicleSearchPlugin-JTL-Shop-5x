<?php

/**
 * Vehicle Search Plugin AJAX Endpoint for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\Frontend
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */

require_once PFAD_ROOT . 'includes/globalinclude.php';

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
            $manufacturers = getManufacturers();
            echo json_encode(['success' => true, 'manufacturers' => $manufacturers]);
            break;
            
        case 'getVehicleModels':
            $manufacturerId = (int)($_POST['manufacturer_id'] ?? 0);
            if ($manufacturerId <= 0) {
                throw new Exception('Invalid manufacturer ID');
            }
            
            $models = getVehicleModelsByManufacturer($manufacturerId);
            echo json_encode(['success' => true, 'models' => $models]);
            break;
            
        case 'getVehicleTypes':
            $modelName = trim($_POST['model_name'] ?? '');
            if (empty($modelName)) {
                throw new Exception('Invalid model name');
            }
            
            $types = getVehicleTypesByModel($modelName);
            echo json_encode(['success' => true, 'types' => $types]);
            break;
            
        case 'getCategories':
            $categories = getCategories();
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;
            
        case 'logSearch':
            $searchData = [
                'search_type' => $_POST['search_type'] ?? '',
                'manufacturer' => $_POST['manufacturer'] ?? null,
                'model' => $_POST['model'] ?? null,
                'vehicle_type' => $_POST['vehicle_type'] ?? null,
                'category' => $_POST['category'] ?? null,
                'results' => (int)($_POST['results'] ?? 0)
            ];
            
            logSearchStats($searchData);
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Get manufacturers from database
 */
function getManufacturers() {
    $sql = "SELECT h.kHersteller, h.cName, h.cBildpfad
            FROM thersteller h
            INNER JOIN tartikel a ON h.kHersteller = a.kHersteller
            WHERE h.nAktiv = 1 
            AND a.nAktiv = 1
            GROUP BY h.kHersteller, h.cName, h.cBildpfad
            ORDER BY h.cName ASC";
    
    $result = Shop::DB()->executeQuery($sql);
    $manufacturers = [];
    
    while ($row = $result->fetch()) {
        $manufacturers[] = [
            'value' => $row['kHersteller'],
            'text' => $row['cName'],
            'image' => $row['cBildpfad']
        ];
    }
    
    return $manufacturers;
}

/**
 * Get vehicle models by manufacturer
 */
function getVehicleModelsByManufacturer($manufacturerId) {
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
    
    $result = Shop::DB()->executeQuery($sql, ['manufacturerId' => $manufacturerId]);
    $models = [];
    
    while ($row = $result->fetch()) {
        $models[] = [
            'value' => $row['cWert'],
            'text' => $row['cWert']
        ];
    }
    
    return $models;
}

/**
 * Get vehicle types by model
 */
function getVehicleTypesByModel($modelName) {
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
    
    $result = Shop::DB()->executeQuery($sql, ['modelName' => '%' . $modelName . '%']);
    $types = [];
    
    while ($row = $result->fetch()) {
        $types[] = [
            'value' => $row['cWert'],
            'text' => $row['cWert']
        ];
    }
    
    return $types;
}

/**
 * Get categories
 */
function getCategories() {
    $sql = "SELECT k.kKategorie, k.cName, k.cBeschreibung, k.nSort, k.kOberKategorie
            FROM tkategorie k
            INNER JOIN tkategorieartikel ka ON k.kKategorie = ka.kKategorie
            INNER JOIN tartikel a ON ka.kArtikel = a.kArtikel
            WHERE k.nAktiv = 1 
            AND a.nAktiv = 1
            GROUP BY k.kKategorie, k.cName, k.cBeschreibung, k.nSort, k.kOberKategorie
            ORDER BY k.nSort ASC, k.cName ASC";
    
    $result = Shop::DB()->executeQuery($sql);
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
    
    return $categories;
}

/**
 * Log search statistics
 */
function logSearchStats($searchData) {
    $sql = "INSERT INTO tplugin_vehicle_search_stats 
            (cSearchType, cManufacturer, cModel, cVehicleType, kKategorie, nResults, cIP, cUserAgent) 
            VALUES (:searchType, :manufacturer, :model, :vehicleType, :category, :results, :ip, :userAgent)";
    
    Shop::DB()->executeQuery($sql, [
        'searchType' => $searchData['search_type'] ?? '',
        'manufacturer' => $searchData['manufacturer'] ?? null,
        'model' => $searchData['model'] ?? null,
        'vehicleType' => $searchData['vehicle_type'] ?? null,
        'category' => $searchData['category'] ?? null,
        'results' => $searchData['results'] ?? 0,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}