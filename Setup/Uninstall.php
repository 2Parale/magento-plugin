<?php

namespace TwoPerformant\BusinessLeagueMarketing\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Remove module data during uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $configTable = $setup->getTable('core_config_data');

        // Remove all module config across default/website/store scopes.
        $pathPrefixes = [
            'twoperformant_identifiers/%',
            'twoperformant_commissions/%',
            'twoperformant/%',
        ];

        foreach ($pathPrefixes as $likePath) {
            $connection->delete($configTable, ['path LIKE ?' => $likePath]);
        }

        $setup->endSetup();
    }
}
