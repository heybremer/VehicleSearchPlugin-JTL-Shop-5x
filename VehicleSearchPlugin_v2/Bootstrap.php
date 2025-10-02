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
        
        // Plugin initialization
        $this->initHooks();
        $this->initServices();
    }
    
    /**
     * Initialize plugin hooks
     */
    private function initHooks(): void
    {
        // Frontend hooks are defined in info.xml
        // Additional hook registrations can be added here if needed
    }
    
    /**
     * Initialize plugin services
     */
    private function initServices(): void
    {
        // Register any services or dependencies here
        // This is where you would typically register services in the DI container
    }
    
    /**
     * @inheritdoc
     */
    public function installed(): void
    {
        parent::installed();
        
        // Plugin installation logic
        $this->createDefaultSettings();
    }
    
    /**
     * @inheritdoc
     */
    public function uninstalled(): void
    {
        parent::uninstalled();
        
        // Plugin uninstallation logic
        $this->cleanupSettings();
    }
    
    /**
     * Create default plugin settings
     */
    private function createDefaultSettings(): void
    {
        // Initialize default settings if needed
    }
    
    /**
     * Cleanup plugin settings
     */
    private function cleanupSettings(): void
    {
        // Cleanup settings if needed
    }
}
