<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;

class TransactionInfoTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TransactionInfo
     */
    private $transactionInfo;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->transactionInfo = $this->objectManager->get(TransactionInfo::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);
    }

    /**
     * Test that the getTransactionInfo method returns the correct data for a real order
     * 
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default/twoperformant_commissions/commissions/category_commissions_enabled 1
     * @magentoConfigFixture default/twoperformant_commissions/commissions/category_commissions_default_commission 5
     */
    public function testGetTransactionInfoReturnsCorrectDataForRealOrder(): void
    {
        // 1. Load the fixture order (ID: 100000001 from standard Magento fixture)
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertNotNull($order->getId(), 'Fixture order not found.');

        // 2. Set the order as the "Last Real Order" in the session
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());

        // 3. Execute the method under test
        $result = $this->transactionInfo->getTransactionInfo();

        // 4. Verify the structure and values
        $this->assertIsArray($result);
        $this->assertEquals('100000001', $result['id']);
        $this->assertEquals('USD', $result['currency_code']);
        
        // Check items
        $this->assertNotEmpty($result['items']);
        $item = $result['items'][0];
        
        $this->assertEquals('Simple Product', $item['name']);
        $this->assertEquals(10.00, $item['value']); // 10.00 price in fixture
        
        // Check commission logic (default 5% from fixture above)
        $this->assertArrayHasKey('commission_percent', $item);
        $this->assertEquals(5.0, $item['commission_percent']);
    }

    protected function tearDown(): void
    {
        $this->checkoutSession->clearStorage();
        parent::tearDown();
    }
}