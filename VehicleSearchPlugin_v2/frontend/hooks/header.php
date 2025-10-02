<?php

namespace Plugin\VehicleSearchPlugin\Frontend\Hooks;

use JTL\Plugin\Hook\HookInterface;
use JTL\Smarty\JTLSmarty;

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
        $smarty = $args['smarty'] ?? null;
        
        if ($smarty instanceof JTLSmarty) {
            // Add CSS and JS resources to header
            $smarty->assign('vehicleSearchPlugin', [
                'css' => $this->getPlugin()->getPaths()->getFrontendPath() . 'css/vehicle-search.css',
                'js' => $this->getPlugin()->getPaths()->getFrontendPath() . 'js/vehicle-search.js'
            ]);
        }
    }
}
