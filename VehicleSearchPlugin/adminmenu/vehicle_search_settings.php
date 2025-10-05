<?php

declare(strict_types=1);

use Plugin\VehicleSearchPlugin\VehicleSearchPlugin;
use JTL\Shop;

require_once PFAD_ROOT . 'admin/includes/admininclude.php';

$pluginHelper = Shop::Container()->getPluginHelper();
$plugin = $pluginHelper->getPluginById(VehicleSearchPlugin::PLUGIN_ID);
if ($plugin === null) {
    die('Vehicle Search Plugin not available.');
}

$service = new VehicleSearchPlugin(
    $plugin,
    Shop::Container()->getDB(),
    Shop::Container()->getCache()
);

$tokenSessionKey = 'vehicle_search_plugin_admin_csrf';
if (empty($_SESSION[$tokenSessionKey]) || !is_string($_SESSION[$tokenSessionKey])) {
    $_SESSION[$tokenSessionKey] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION[$tokenSessionKey], $postedToken)) {
        $error = 'Ungültiges Sicherheits-Token.';
    } else {
        if (isset($_POST['clear_cache'])) {
            try {
                $service->clearCache();
                $success = 'Der Cache wurde geleert.';
            } catch (Throwable $throwable) {
                $error = 'Cache konnte nicht geleert werden: ' . $throwable->getMessage();
            }
        } else {
            $configs = [
                'enable_ajax' => isset($_POST['enable_ajax']) ? '1' : '0',
                'default_search_type' => in_array($_POST['default_search_type'] ?? 'M', ['M', 'K'], true) ? $_POST['default_search_type'] : 'M',
                'max_results_per_page' => max(1, (int)($_POST['max_results_per_page'] ?? 20)),
                'enable_manufacturer_filter' => isset($_POST['enable_manufacturer_filter']) ? '1' : '0',
                'enable_model_filter' => isset($_POST['enable_model_filter']) ? '1' : '0',
                'enable_type_filter' => isset($_POST['enable_type_filter']) ? '1' : '0',
                'enable_category_filter' => isset($_POST['enable_category_filter']) ? '1' : '0',
                'cache_duration' => max(60, (int)($_POST['cache_duration'] ?? 3600)),
                'show_vehicle_images' => isset($_POST['show_vehicle_images']) ? '1' : '0',
                'enable_advanced_search' => isset($_POST['enable_advanced_search']) ? '1' : '0',
            ];

            try {
                foreach ($configs as $key => $value) {
                    $service->setConfig($key, $value);
                }

                $success = 'Einstellungen wurden gespeichert.';
            } catch (Throwable $throwable) {
                $error = 'Einstellungen konnten nicht gespeichert werden: ' . $throwable->getMessage();
            }
        }
    }
}

$config = [
    'enable_ajax' => '1',
    'default_search_type' => 'M',
    'max_results_per_page' => '20',
    'enable_manufacturer_filter' => '1',
    'enable_model_filter' => '1',
    'enable_type_filter' => '1',
    'enable_category_filter' => '1',
    'cache_duration' => '3600',
    'show_vehicle_images' => '1',
    'enable_advanced_search' => '1',
];

foreach (array_keys($config) as $key) {
    $value = $service->getConfig($key, $config[$key]);
    $config[$key] = is_string($value) ? $value : (string)$value;
}

$smarty = Shop::Smarty();
$smarty->assign('config', $config);
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('csrfToken', $_SESSION[$tokenSessionKey]);
$smarty->assign('pluginUrl', $plugin->getPaths()->getBaseURL());

echo $smarty->fetch('adminmenu/vehicle_search_settings.tpl');

