<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\View;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class CheckoutSuccessTrackingTest extends AbstractController
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/big_bear_unique TEST_ID_123
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/campaign_unique CAMP123
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/confirm CONF123
     */
    public function testTrackerMarkupRendersOnSuccessPage(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $quoteId = (int) ($order->getQuoteId() ?: 1);

        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderId((int) $order->getId());
        $this->checkoutSession->setLastQuoteId($quoteId);
        $this->checkoutSession->setLastSuccessQuoteId($quoteId);

        $this->dispatch('checkout/onepage/success');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('var tpOrder', $body);
        $this->assertStringContainsString('/TEST_ID_123/sls/1.js', $body);
        $this->assertStringContainsString('event.2performant.com', $body);
        $this->assertStringContainsString('campaign_unique=CAMP123', $body);
    }

    protected function tearDown(): void
    {
        $this->checkoutSession->clearStorage();
        parent::tearDown();
    }
}