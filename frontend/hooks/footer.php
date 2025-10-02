<?php

namespace Plugin\VehicleSearchPlugin\Frontend\Hooks;

use JTL\Plugin\Hook\HookInterface;

/**
 * Class Footer
 * @package Plugin\VehicleSearchPlugin\Frontend\Hooks
 */
class Footer implements HookInterface
{
    /**
     * @inheritdoc
     */
    public function execute(array $args): void
    {
        // Add JavaScript resources to footer
        $plugin = $this->getPlugin();
        if ($plugin) {
            $pluginUrl = $plugin->getPaths()->getFrontendPath();
            echo '<script src="' . $pluginUrl . 'js/vehicle-search.js" type="text/javascript"></script>';
        }
    }
}