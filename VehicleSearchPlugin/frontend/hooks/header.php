<?php

declare(strict_types=1);

use JTL\Shop;
use JTL\Plugin\PluginInterface;

/** @var array<string, mixed> $args */
$plugin = $args['plugin'] ?? null;
if (!$plugin instanceof PluginInterface) {
    $plugin = Shop::Container()->getPluginHelper()->getPluginById('VehicleSearchPlugin');
}

if ($plugin === null) {
    return;
}

$path = $plugin->getPaths()->getFrontendPath();
$stylesheet = $path . 'css/vehicle-search.css';

echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') . '" />';

