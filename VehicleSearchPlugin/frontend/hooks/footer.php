<?php

declare(strict_types=1);

use JTL\Plugin\PluginInterface;
use JTL\Shop;

/** @var array<string, mixed> $args */
$plugin = $args['plugin'] ?? null;
if (!$plugin instanceof PluginInterface) {
    $plugin = Shop::Container()->getPluginHelper()->getPluginById('VehicleSearchPlugin');
}

if ($plugin === null) {
    return;
}

$sessionKey = 'vehicle_search_plugin_csrf';
if (empty($_SESSION[$sessionKey]) || !is_string($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = bin2hex(random_bytes(32));
}

$frontendPath = $plugin->getPaths()->getFrontendPath();
$config = [
    'pluginUrl' => $frontendPath,
    'csrfToken' => $_SESSION[$sessionKey],
];

echo '<script type="text/javascript">';
echo 'window.VehicleSearchPlugin = Object.assign({}, window.VehicleSearchPlugin || {}, ';
echo json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
echo ');';
echo '</script>';
echo '<script src="' . htmlspecialchars($frontendPath . 'js/vehicle-search.js', ENT_QUOTES, 'UTF-8') . '" type="text/javascript"></script>';

