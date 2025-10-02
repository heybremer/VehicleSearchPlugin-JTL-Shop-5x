<?php

/**
 * Vehicle Search Plugin Admin Settings for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\AdminMenu
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */

require_once PFAD_ROOT . 'admin/includes/admininclude.php';

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    try {
        // Update configuration
        $configs = [
            'enable_ajax' => $_POST['enable_ajax'] ?? '0',
            'default_search_type' => $_POST['default_search_type'] ?? 'M',
            'max_results_per_page' => (int)($_POST['max_results_per_page'] ?? 20),
            'enable_manufacturer_filter' => $_POST['enable_manufacturer_filter'] ?? '0',
            'enable_model_filter' => $_POST['enable_model_filter'] ?? '0',
            'enable_type_filter' => $_POST['enable_type_filter'] ?? '0',
            'enable_category_filter' => $_POST['enable_category_filter'] ?? '0',
            'cache_duration' => (int)($_POST['cache_duration'] ?? 3600),
            'show_vehicle_images' => $_POST['show_vehicle_images'] ?? '0',
            'enable_advanced_search' => $_POST['enable_advanced_search'] ?? '0'
        ];
        
        foreach ($configs as $key => $value) {
            $sql = "INSERT INTO tplugin_vehicle_search_config (cName, cValue) 
                    VALUES (:key, :value) 
                    ON DUPLICATE KEY UPDATE cValue = :value";
            
            Shop::DB()->executeQuery($sql, ['key' => $key, 'value' => $value]);
        }
        
        $success = 'Settings saved successfully!';
    } catch (Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}

// Handle cache clear
if (isset($_POST['clear_cache'])) {
    try {
        Shop::DB()->executeQuery("DELETE FROM tplugin_vehicle_search_cache WHERE dExpires < NOW()");
        $success = 'Cache cleared successfully!';
    } catch (Exception $e) {
        $error = 'Error clearing cache: ' . $e->getMessage();
    }
}

// Get current configuration
$config = [];
$sql = "SELECT cName, cValue FROM tplugin_vehicle_search_config";
$result = Shop::DB()->executeQuery($sql);

while ($row = $result->fetch()) {
    $config[$row['cName']] = $row['cValue'];
}

// Set defaults
$config = array_merge([
    'enable_ajax' => '1',
    'default_search_type' => 'M',
    'max_results_per_page' => '20',
    'enable_manufacturer_filter' => '1',
    'enable_model_filter' => '1',
    'enable_type_filter' => '1',
    'enable_category_filter' => '1',
    'cache_duration' => '3600',
    'show_vehicle_images' => '1',
    'enable_advanced_search' => '1'
], $config);

$smarty = new Smarty();
$smarty->assign('config', $config);
$smarty->assign('error', $error);
$smarty->assign('success', $success);

echo $smarty->fetch('adminmenu/vehicle_search_settings.tpl');