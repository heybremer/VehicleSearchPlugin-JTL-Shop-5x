<?php

namespace Plugin\VehicleSearchPlugin;

use JTL\Plugin\Bootstrapper;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use PDO;
use Throwable;

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

        $this->disableOrphanedJtlSearchCronJob();
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

    private function disableOrphanedJtlSearchCronJob(): void
    {
        try {
            $container = Shop::Container();
            $pluginHelper = $container->getPluginHelper();

            if ($pluginHelper->getPluginById('jtlsearch') !== null) {
                return;
            }

            $pdo = $container->getDB()->getPDO();
            $select = $pdo->prepare('SELECT kCron, nAktiv FROM tcron WHERE cJobType = :type LIMIT 1');
            $select->execute(['type' => 'jtl_search_full_export']);
            $cron = $select->fetch(PDO::FETCH_OBJ);

            if ($cron === false || (int)($cron->nAktiv ?? 0) === 0) {
                return;
            }

            $update = $pdo->prepare('UPDATE tcron SET nAktiv = 0 WHERE kCron = :id');
            $update->execute(['id' => (int)$cron->kCron]);

            $logger = $container->getLogService();
            $logger->notice('VehicleSearchPlugin disabled orphaned cron job jtl_search_full_export.');
        } catch (Throwable $throwable) {
            try {
                $container = Shop::Container();
                $logger = $container->getLogService();
                $logger->warning(
                    sprintf(
                        'VehicleSearchPlugin could not adjust cron job jtl_search_full_export: %s',
                        $throwable->getMessage()
                    )
                );
            } catch (Throwable $ignored) {
                // Ignore logging failures silently.
            }
        }
    }
}