<?php

declare(strict_types=1);

use JTL\Shop;
use Plugin\VehicleSearchPlugin\VehicleSearchPlugin;

require_once PFAD_ROOT . 'includes/globalinclude.php';

header('Content-Type: application/json; charset=utf-8');

if (strcasecmp($_SERVER['REQUEST_METHOD'] ?? 'GET', 'POST') !== 0) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isAjaxRequest()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request type']);
    exit;
}

$csrfToken = (string)($_POST['csrf_token'] ?? '');
$sessionToken = (string)($_SESSION['vehicle_search_plugin_csrf'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$pluginHelper = Shop::Container()->getPluginHelper();
$plugin = $pluginHelper->getPluginById(VehicleSearchPlugin::PLUGIN_ID);
if ($plugin === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Plugin context unavailable']);
    exit;
}

$service = new VehicleSearchPlugin(
    $plugin,
    Shop::Container()->getDB(),
    Shop::Container()->getCache()
);

$action = trim((string)($_POST['action'] ?? ''));

try {
    switch ($action) {
        case 'getManufacturers':
            respond(['manufacturers' => $service->getManufacturers()]);
            break;

        case 'getVehicleModels':
            $manufacturerId = filter_input(INPUT_POST, 'manufacturer_id', FILTER_VALIDATE_INT);
            if ($manufacturerId === false || $manufacturerId <= 0) {
                throw new InvalidArgumentException('Invalid manufacturer identifier');
            }

            respond(['models' => $service->getVehicleModelsByManufacturer($manufacturerId)]);
            break;

        case 'getVehicleTypes':
            $modelName = filter_input(INPUT_POST, 'model_name', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            $modelName = is_string($modelName) ? trim($modelName) : '';
            if ($modelName === '') {
                throw new InvalidArgumentException('Invalid model name');
            }

            respond(['types' => $service->getVehicleTypesByModel($modelName)]);
            break;

        case 'getCategories':
            respond(['categories' => $service->getCategories()]);
            break;

        case 'logSearch':
            $searchData = [
                'searchType' => sanitizeString($_POST['search_type'] ?? ''),
                'manufacturer' => sanitizeString($_POST['manufacturer'] ?? null),
                'model' => sanitizeString($_POST['model'] ?? null),
                'vehicleType' => sanitizeString($_POST['vehicle_type'] ?? null),
                'category' => sanitizeString($_POST['category'] ?? null),
                'results' => filter_input(INPUT_POST, 'results', FILTER_VALIDATE_INT) ?: 0,
            ];

            $service->logSearchStats($searchData);
            respond();
            break;

        default:
            throw new InvalidArgumentException('Unknown action');
    }
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
} catch (Throwable $throwable) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unexpected error']);
}

function respond(array $payload = []): void
{
    echo json_encode(['success' => true] + $payload);
    exit;
}

function sanitizeString($value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    return $value === '' ? null : $value;
}

function isAjaxRequest(): bool
{
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    return is_string($requestedWith) && strcasecmp($requestedWith, 'XMLHttpRequest') === 0;
}

