<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\ViewModel;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\PixelTracker;

class PixelTrackerTest extends TestCase
{
    /**
     * @var PixelTracker
     */
    private $viewModel;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->viewModel = $objectManager->get(PixelTracker::class);
        $this->checkoutSession = $objectManager->get(CheckoutSession::class);
    }

    public function testGetIframeUrlReturnsEmptyWhenIdentifiersMissing(): void
    {
        $this->assertSame('', $this->viewModel->getIframeUrl());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/campaign_unique CAMP123
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/confirm CONF123
     */
    public function testGetIframeUrlBuildsQueryForOrder(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());

        $url = $this->viewModel->getIframeUrl();
        $this->assertNotEmpty($url);

        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $this->assertSame('CAMP123', $query['campaign_unique'] ?? null);
        $this->assertSame('CONF123', $query['confirm'] ?? null);
        $this->assertArrayHasKey('value', $query);
        $this->assertArrayHasKey('description', $query);
    }

    protected function tearDown(): void
    {
        $this->checkoutSession->clearStorage();
        parent::tearDown();
    }
}
