<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ModuleEnablementTest extends TestCase
{
    /**
     * Test that the module is registered with the ComponentRegistrar.
     * This means registration.php is loaded.
     */
    public function testModuleIsRegistered(): void
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey('TwoPerformant_BusinessLeagueMarketing', $paths);
    }

    /**
     * Test that the module is enabled in the real Magento configuration.
     */
    public function testModuleIsEnabled(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ModuleList $moduleList */
        $moduleList = $objectManager->get(ModuleList::class);
        
        $this->assertTrue(
            $moduleList->has('TwoPerformant_BusinessLeagueMarketing'), 
            'The module TwoPerformant_BusinessLeagueMarketing is not enabled.'
        );
    }
}