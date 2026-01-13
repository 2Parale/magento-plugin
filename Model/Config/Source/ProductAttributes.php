<?php
namespace TwoPerformant\BusinessLeagueMarketing\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product attributes source model for the brand attribute name
 *
 * @since 1.0.0
 */
class ProductAttributes implements OptionSourceInterface
{
    private CollectionFactory $attributeCollectionFactory;
    private StoreManagerInterface $storeManager;

    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function toOptionArray(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        $collection = $this->attributeCollectionFactory->create()
            ->addVisibleFilter()            // only visible attributes
            ->setOrder('frontend_label', 'ASC')
            ->setItemObjectClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

        // Ensure labels are scoped
        // $collection->setStoreId($storeId);

        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        foreach ($collection as $attribute) {
            $label = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();
            if (!$label) {
                continue;
            }
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $label,
            ];
        }

        return $options;
    }
}