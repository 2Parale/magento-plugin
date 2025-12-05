<?php
namespace TwoPerformant\BusinessLeagueMarketing\Model;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

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
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get the transaction info for the last order
     *
     * @return array|null
     */
    public function getTransactionInfo(): array|null
    {
        // get the order object
        $order = $this->checkoutSession->getLastRealOrder();;
        if(!$order){
            return null;
        }

        // get info about the order items
        $items = $this->processOrderItems($order);

        // get the order id, the time it was placed and the currency code
        $id = (string) $order->getIncrementId();
        $placedAt = (string) (int) strtotime($order->getCreatedAt());
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
     * @param Magento\Sales\Model\Order\Interceptor $order
     * @return array
     */
    private function processOrderItems(\Magento\Sales\Model\Order\Interceptor $order): array
    {

        // initialize the items array
        $items = [];

        // loop through the order items
        foreach($order->getItems() as $item){
            // get the price of the item without taxes
            $value = $item->getPrice();

            // get the product object
            $product = $item->getProduct();

            // get the category collection and load the category names
            $categoryCollection = $product->getCategoryCollection();
            $categoryCollection->addAttributeToSelect('name');
            // initialize the categories array
            $categories = [];
            // loop through the categories and add the names to the array
            foreach($categoryCollection as $category){
                if($category->getName()){
                    $categories[] = $category->getName();
                }
            }

            // get the brand attribute and the brand value
            $brandAttribute = $product->getResource()->getAttribute('brand');
            $brand = null;

            // if the brand attribute uses a source (Dropdown/Multiselect), get the brand value
            if ($brandAttribute && $brandAttribute->usesSource()) {
                // Safe to call getAttributeText only if it uses a source (Dropdown/Multiselect)
                $brand = $product->getAttributeText('brand');
            }
            
            // If null (e.g., it's a text attribute, not a dropdown), get the raw value
            if ($brand === null) {
                $brand = $product->getData('brand');
            }

            // Handle cases where brand might be an array (multiselect)
            if (is_array($brand)) {
                $brand = implode(', ', $brand);
            }

            // constract the array containing the item info and add it to the items array
            $item = [
                'product_id' =>(string) $item->getProductId(),
                'name' => (string) $item->getName(),
                'quantity' => (int) $item->getQtyOrdered(),
                'value' => number_format((float)$value, 2, '.', ''),
                'category_name' => $categories,
                'brand' => $brand ? (string) $brand : '',
            ];
            $items[] = $item;
        }

        // return the items array to be included in the transaction info
        return $items;
    }
}
