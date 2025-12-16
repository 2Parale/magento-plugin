<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\Category;

class TransactionInfoTest extends TestCase
{
    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var TransactionInfo
     */
    private $transactionInfo;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->transactionInfo = new TransactionInfo($this->checkoutSessionMock);
    }

    public function testGetTransactionInfoReturnsNullWhenNoOrder()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn(null);

        $this->assertNull($this->transactionInfo->getTransactionInfo());
    }

    /**
     * Test full transaction flow with brand as a source attribute (dropdown)
     */
    public function testGetTransactionInfoWithOrderAndSourceBrand()
    {
        // 1. Mock Order
        $orderMock = $this->createMock(Order::class);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($orderMock);

        $orderMock->method('getIncrementId')->willReturn('10000001');
        $orderMock->method('getCreatedAt')->willReturn('2023-01-01 12:00:00');
        $orderMock->method('getOrderCurrencyCode')->willReturn('USD');

        // 2. Mock Item
        $itemMock = $this->createMock(Item::class);
        $orderMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);

        $itemMock->method('getPrice')->willReturn(100.00);
        $itemMock->method('getProductId')->willReturn('99');
        $itemMock->method('getName')->willReturn('Test Product');
        $itemMock->method('getQtyOrdered')->willReturn(2);

        // 3. Mock Product
        $productMock = $this->createMock(Product::class);
        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);

        // 4. Mock Categories
        $categoryCollectionMock = $this->createMock(CategoryCollection::class);
        $productMock->expects($this->once())->method('getCategoryCollection')->willReturn($categoryCollectionMock);

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getName')->willReturn('Electronics');

        // Setup Iterator for Collection to return our category mock
        $categoryCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$categoryMock]));

        // 5. Mock Brand (Source Attribute)
        $resourceMock = $this->createMock(ProductResource::class);
        $productMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock->expects($this->once())->method('getAttribute')->with('brand')->willReturn($attributeMock);
        
        // Brand uses source (e.g. Dropdown)
        $attributeMock->method('usesSource')->willReturn(true);
        $productMock->expects($this->once())->method('getAttributeText')->with('brand')->willReturn('Sony');

        // Execute
        $result = $this->transactionInfo->getTransactionInfo();

        // Assertions
        $this->assertIsArray($result);
        $this->assertEquals('10000001', $result['id']);
        $this->assertEquals(strtotime('2023-01-01 12:00:00'), $result['placed_at']);
        $this->assertEquals('USD', $result['currency_code']);
        
        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertEquals('99', $item['product_id']);
        $this->assertEquals('Test Product', $item['name']);
        $this->assertEquals(2, $item['quantity']);
        $this->assertEquals('100.00', $item['value']); // Number format 2 decimals
        $this->assertEquals(['Electronics'], $item['category_name']);
        $this->assertEquals('Sony', $item['brand']);
    }

    /**
     * Test flow where brand is a text attribute (not source/dropdown)
     */
    public function testGetTransactionInfoWithTextBrand()
    {
        $orderMock = $this->createMock(Order::class);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($orderMock);

        $itemMock = $this->createMock(Item::class);
        $orderMock->method('getItems')->willReturn([$itemMock]);
        
        $productMock = $this->createMock(Product::class);
        $itemMock->method('getProduct')->willReturn($productMock);
        
        // Mock empty categories
        $categoryCollectionMock = $this->createMock(CategoryCollection::class);
        $categoryCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));
        $productMock->method('getCategoryCollection')->willReturn($categoryCollectionMock);

        // Mock Brand (Text Attribute)
        $resourceMock = $this->createMock(ProductResource::class);
        $productMock->method('getResource')->willReturn($resourceMock);

        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock->method('getAttribute')->with('brand')->willReturn($attributeMock);

        // Brand does NOT use source
        $attributeMock->method('usesSource')->willReturn(false);
        // Should fetch via getData
        $productMock->expects($this->once())->method('getData')->with('brand')->willReturn('Generic Brand');

        $result = $this->transactionInfo->getTransactionInfo();

        $this->assertEquals('Generic Brand', $result['items'][0]['brand']);
    }
}
