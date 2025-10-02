<?php

namespace Plugin\VehicleSearchPlugin;

use JTL\Plugin\Bootstrapper;
use JTL\Plugin\PluginInterface;

/**
 * Class Bootstrap
 * @package Plugin\VehicleSearchPlugin
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function boot(PluginInterface $plugin): void
    {
        parent::boot($plugin);
    }
    
    /**
     * @inheritdoc
     */
    public function installed(): void
    {
        parent::installed();
    }
    
    /**
     * @inheritdoc
     */
    public function uninstalled(): void
    {
        parent::uninstalled();
    }
}