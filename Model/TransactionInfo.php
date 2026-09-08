<?php
namespace TwoPerformant\BusinessLeagueMarketing\Model;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * TransactionInfo model for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class TransactionInfo implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Get the transaction info for the last order
     *
     * @return array|null
     */
    public function getTransactionInfo(): array|null
    {
        // get the order object
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order) {
            return null;
        }

        // get info about the order items
        $items = $this->processOrderItems($order);

        // get the order id, the time it was placed and the currency code
        $id = (string) $order->getIncrementId();
        $createdAt = $order->getCreatedAt();
        $placedAt = $createdAt ? (int) strtotime($createdAt) : 0;
        $currencyCode = (string) $order->getOrderCurrencyCode();

        // return the transaction info to be used by the view models
        return [
            'id' => $id,
            'placed_at' => $placedAt,
            'currency_code' => $currencyCode,
            'items' => $items,
        ];
    }

    /**
     * Process the order items
     *
     * @param Magento\Sales\Model\Order $order
     * @return array
     */
    private function processOrderItems(\Magento\Sales\Model\Order $order): array
    {
        $itemsResult = [];
        $orderItems = $order->getAllVisibleItems();

        if (empty($orderItems)) {
            return [];
        }

        // gather all Product IDs from the order
        $productIds = [];
        foreach ($orderItems as $item) {
            $productIds[] = $item->getProductId();
        }

        if (empty($productIds)) {
            return [];
        }

        // load all Products in one query (with name, category_ids and brand attributes)
        $brandAttributeName = $this->config->getBrandAttributeName();
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('name');

        if ($brandAttributeName) {
            $productCollection->addAttributeToSelect($brandAttributeName);
        }
        $productCollection->addIdFilter($productIds);

        // if the existing Magento version has the method addCategoryIds,
        // use it in order to completely avoid the N+1 query problem with getting category ids
        if (method_exists($productCollection, 'addCategoryIds')) {
            $productCollection->addCategoryIds();
        }
        
        // map loaded products by ID for easy lookup
        $loadedProducts = [];
        $allCategoryIds = [];
        
        foreach ($productCollection as $product) {
            $loadedProducts[$product->getId()] = $product;
            
            // collect Category IDs from the loaded product
            $catIds = $product->getCategoryIds();
            if (!empty($catIds)) {
                $allCategoryIds = array_merge($allCategoryIds, $catIds);
            }
        }

        // load all Categories in one query (ID => Name map)
        $categoryNamesMap = [];
        $categoryRootIdMap = [];
        if (!empty($allCategoryIds)) {
            $allCategoryIds = array_unique($allCategoryIds);
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection->addAttributeToSelect('name');
            $categoryCollection->addIdFilter($allCategoryIds);
            
            foreach ($categoryCollection as $category) {
                $categoryNamesMap[$category->getId()] = $category->getName();
                $categoryRootIdMap[(int)$category->getId()] = $this->resolveRootCategoryId((string)$category->getPath());
            }
        }

        // config data for loop
        $categoryCommissionsEnabled = $this->config->getCategoryCommissionsEnabled();
        $specialCategoryCommissions = $categoryCommissionsEnabled ? $this->config->getCategoryCommissions() : [];
        
        // normalize commission map keys to int
        $specialCategoryCommissionsById = [];
        foreach ($specialCategoryCommissions as $categoryId => $commission) {
            $specialCategoryCommissionsById[(int)$categoryId] = (float)$commission;
        }
        // build final array
        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            
            // skip if product not found
            if (!isset($loadedProducts[$productId])) {
                continue;
            }
            
            $product = $loadedProducts[$productId];
            
            // resolve categories names and commissions
            $itemCategoryNames = [];
            $commissionValue = null;
            
            $productCatIds = $product->getCategoryIds();
            foreach ($productCatIds as $catIdRaw) {
                $catId = (int)$catIdRaw;
                // get name from the bulk-loaded map
                if (isset($categoryNamesMap[$catId])) {
                    $itemCategoryNames[] = $categoryNamesMap[$catId];
                }
                
                // check if the category is in the special commission categories
                $rootCatId = $categoryRootIdMap[$catId] ?? null;
                if ($categoryCommissionsEnabled && $rootCatId !== null && isset($specialCategoryCommissionsById[$rootCatId])) {
                    $catCommission = $specialCategoryCommissionsById[$rootCatId];
                    if ($commissionValue === null || $catCommission < $commissionValue) {
                        $commissionValue = $catCommission;
                    }
                }
            }

            // resolve brand
            $brand = null;
            if ($brandAttributeName) {
                // try to get text (for dropdowns)
                $brand = $product->getAttributeText($brandAttributeName);
                
                // if not dropdown/text, get raw data
                if (!$brand) {
                    $brand = $product->getData($brandAttributeName);
                }
                
                if (is_array($brand)) {
                    $brand = implode(', ', $brand);
                }
            }

            // calculate net value with discount
            $discountAmount = (float) $item->getDiscountAmount();
            $qtyOrdered = (int) $item->getQtyOrdered();
            $unitDiscount = $qtyOrdered > 0 ? $discountAmount / $qtyOrdered : 0.0;
            $netValue = (float)$item->getPrice() - $unitDiscount;

            // build item
            $itemData = [
                'product_id' => (string) $item->getProductId(),
                'name' => (string) $item->getName(),
                'quantity' => $qtyOrdered,
                'value' => number_format($netValue, 2, '.', ''),
                'category' => $itemCategoryNames,
                'brand' => $brand ? (string) $brand : '',
            ];

            if ($categoryCommissionsEnabled) {
                $commission = $commissionValue !== null
                    ? $commissionValue
                    : (float) $this->config->getDefaultCommissionValue();
                $itemData['commission_percent'] = (float) $commission;
            }

            $itemsResult[] = $itemData;
        }

        return $itemsResult;
    }

    /**
     * Resolve the top-level "root" category ID (level 2) from a category's path
     *
     * @param string $path
     * @return int|null
     */
    private function resolveRootCategoryId(string $path): ?int
    {
        $segments = explode('/', $path);
        return isset($segments[2]) ? (int) $segments[2] : null;
    }
}
