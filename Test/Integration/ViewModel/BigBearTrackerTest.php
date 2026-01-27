<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\ViewModel;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\BigBearTracker;

class BigBearTrackerTest extends TestCase
{
    /**
     * @var BigBearTracker
     */
    private $viewModel;

    protected function setUp(): void
    {
        $this->viewModel = Bootstrap::getObjectManager()->get(BigBearTracker::class);
    }

    /**
     * Test that the getSlsUrl method returns the correct URL when the big bear unique identifier is configured
     * 
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/big_bear_unique TEST_ID_123
     */
    public function testGetSlsUrlReturnsCorrectUrlWhenConfigured(): void
    {
        $url = $this->viewModel->getSlsUrl();
        // The default implementation in Config model usually constructs this.
        // Assuming default structure or valid return based on config.
        $this->assertStringContainsString('/TEST_ID_123/sls/1.js', $url);
    }

    /**
     * Test that the getSlsUrl method returns an empty string when the big bear unique identifier is not configured
     */
    public function testGetSlsUrlReturnsEmptyWhenNotConfigured(): void
    {
        // Ensure config is empty
        $url = $this->viewModel->getSlsUrl();
        $this->assertEmpty($url);
    }
}