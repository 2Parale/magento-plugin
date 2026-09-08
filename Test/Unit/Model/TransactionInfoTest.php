<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\Category;

class TransactionInfoTest extends TestCase
{
    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var CategoryCollectionFactory|MockObject
     */
    private $categoryCollectionFactoryMock;

    /**
     * @var TransactionInfo
     */
    private $transactionInfo;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->configMock = $this->createMock(Config::class);
        $this->productCollectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->categoryCollectionFactoryMock = $this->createMock(CategoryCollectionFactory::class);

        $this->transactionInfo = new TransactionInfo(
            $this->checkoutSessionMock,
            $this->configMock,
            $this->productCollectionFactoryMock,
            $this->categoryCollectionFactoryMock
        );
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
        $this->configMock->method('getBrandAttributeName')->willReturn('brand');

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
        $orderMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$itemMock]);

        $itemMock->method('getPrice')->willReturn(100.00);
        $itemMock->method('getDiscountAmount')->willReturn(0.0);
        $itemMock->method('getProductId')->willReturn('99');
        $itemMock->method('getName')->willReturn('Test Product');
        $itemMock->method('getQtyOrdered')->willReturn(2);

        // 3. Mock Product Collection + Product
        $productCollectionMock = $this->createMock(ProductCollection::class);
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productCollectionMock);

        $productCollectionMock->method('addAttributeToSelect')->willReturnSelf();
        $productCollectionMock->method('addIdFilter')->willReturnSelf();

        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(99);
        $productMock->method('getCategoryIds')->willReturn([7]);
        $productMock->method('getAttributeText')->with('brand')->willReturn('Sony');

        $productCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$productMock]));

        // 4. Mock Category Collection
        $categoryCollectionMock = $this->createMock(CategoryCollection::class);
        $this->categoryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryCollectionMock);

        $categoryCollectionMock->method('addAttributeToSelect')->willReturnSelf();
        $categoryCollectionMock->method('addIdFilter')->willReturnSelf();

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(7);
        $categoryMock->method('getName')->willReturn('Electronics');
        $categoryMock->method('getPath')->willReturn('1/2/7');

        $categoryCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$categoryMock]));

        // Execute
        $result = $this->transactionInfo->getTransactionInfo();

        // Assertions
        $this->assertIsArray($result);
        $this->assertEquals('10000001', $result['id']);
        $this->assertEquals((int) strtotime('2023-01-01 12:00:00'), $result['placed_at']);
        $this->assertEquals('USD', $result['currency_code']);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertEquals('99', $item['product_id']);
        $this->assertEquals('Test Product', $item['name']);
        $this->assertEquals(2, $item['quantity']);
        $this->assertEquals('100.00', $item['value']); // Number format 2 decimals
        $this->assertEquals(['Electronics'], $item['category']);
        $this->assertEquals('Sony', $item['brand']);
    }

    /**
     * Test flow where brand is a text attribute (not source/dropdown)
     */
    public function testGetTransactionInfoWithTextBrand()
    {
        $this->configMock->method('getBrandAttributeName')->willReturn('brand');

        $orderMock = $this->createMock(Order::class);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($orderMock);

        $itemMock = $this->createMock(Item::class);
        $orderMock->method('getAllVisibleItems')->willReturn([$itemMock]);

        $itemMock->method('getPrice')->willReturn(50.00);
        $itemMock->method('getDiscountAmount')->willReturn(0.0);
        $itemMock->method('getProductId')->willReturn('99');
        $itemMock->method('getName')->willReturn('Test Product');
        $itemMock->method('getQtyOrdered')->willReturn(1);

        $productCollectionMock = $this->createMock(ProductCollection::class);
        $this->productCollectionFactoryMock->method('create')
            ->willReturn($productCollectionMock);

        $productCollectionMock->method('addAttributeToSelect')->willReturnSelf();
        $productCollectionMock->method('addIdFilter')->willReturnSelf();

        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(99);
        $productMock->method('getCategoryIds')->willReturn([]);
        $productMock->method('getAttributeText')->with('brand')->willReturn(null);
        $productMock->method('getData')->with('brand')->willReturn('Generic Brand');

        $productCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$productMock]));

        $result = $this->transactionInfo->getTransactionInfo();

        $this->assertEquals('Generic Brand', $result['items'][0]['brand']);
    }

    /**
     * Test that a product assigned only to a subcategory still gets its root ancestor's commission
     */
    public function testGetTransactionInfoAppliesRootCategoryCommissionForSubcategoryProduct()
    {
        $this->configMock->method('getCategoryCommissionsEnabled')->willReturn(true);
        $this->configMock->method('getCategoryCommissions')->willReturn([7 => 10.0]);
        $this->configMock->method('getDefaultCommissionValue')->willReturn(2.0);

        $orderMock = $this->createMock(Order::class);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($orderMock);
        $orderMock->method('getIncrementId')->willReturn('10000002');
        $orderMock->method('getCreatedAt')->willReturn('2023-01-01 12:00:00');
        $orderMock->method('getOrderCurrencyCode')->willReturn('USD');

        $itemMock = $this->createMock(Item::class);
        $orderMock->method('getAllVisibleItems')->willReturn([$itemMock]);
        $itemMock->method('getPrice')->willReturn(20.00);
        $itemMock->method('getDiscountAmount')->willReturn(0.0);
        $itemMock->method('getProductId')->willReturn('101');
        $itemMock->method('getName')->willReturn('Sub Product');
        $itemMock->method('getQtyOrdered')->willReturn(1);

        $productCollectionMock = $this->createMock(ProductCollection::class);
        $this->productCollectionFactoryMock->method('create')->willReturn($productCollectionMock);
        $productCollectionMock->method('addAttributeToSelect')->willReturnSelf();
        $productCollectionMock->method('addIdFilter')->willReturnSelf();

        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(101);
        // assigned only to the leaf category, not its root ancestor
        $productMock->method('getCategoryIds')->willReturn([9]);

        $productCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$productMock]));

        $categoryCollectionMock = $this->createMock(CategoryCollection::class);
        $this->categoryCollectionFactoryMock->method('create')->willReturn($categoryCollectionMock);
        $categoryCollectionMock->method('addAttributeToSelect')->willReturnSelf();
        $categoryCollectionMock->method('addIdFilter')->willReturnSelf();

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(9);
        $categoryMock->method('getName')->willReturn('Leaf Category');
        // root catalog(1) / store root(2) / root category(7) / leaf category(9)
        $categoryMock->method('getPath')->willReturn('1/2/7/9');

        $categoryCollectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator([$categoryMock]));

        $result = $this->transactionInfo->getTransactionInfo();

        $this->assertEquals(10.0, $result['items'][0]['commission_percent']);
    }
}
