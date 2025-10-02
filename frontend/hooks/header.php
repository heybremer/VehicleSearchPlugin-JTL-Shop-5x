<?php

namespace Plugin\VehicleSearchPlugin\Frontend\Hooks;

use JTL\Plugin\Hook\HookInterface;

/**
 * Class Header
 * @package Plugin\VehicleSearchPlugin\Frontend\Hooks
 */
class Header implements HookInterface
{
    /**
     * @inheritdoc
     */
    public function execute(array $args): void
    {
        // Add CSS and JS resources to header
        $plugin = $this->getPlugin();
        if ($plugin) {
            $pluginUrl = $plugin->getPaths()->getFrontendPath();
            echo '<link rel="stylesheet" href="' . $pluginUrl . 'css/vehicle-search.css" type="text/css" />';
        }
    }
}