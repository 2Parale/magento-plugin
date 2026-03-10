<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\View;

use Magento\TestFramework\TestCase\AbstractController;

class CreditsFooterRenderingTest extends AbstractController
{
    /**
     * The credits block uses default.xml, so it renders on every frontend page.
     * We dispatch the homepage as a lightweight representative request.
     *
     * @magentoAppArea frontend
     */
    public function testCreditsBlockRendersInFooterWithDefaultConfig(): void
    {
        $this->dispatch('/');

        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('twoperformant-credits', $body);
        $this->assertStringContainsString('Active in', $body);
        $this->assertStringContainsString('BusinessLeague', $body);
        $this->assertStringContainsString('https://businessleague.com/', $body);
        $this->assertStringContainsString('rel="sponsored noopener noreferrer"', $body);
    }
}
