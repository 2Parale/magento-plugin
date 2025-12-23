<?php
namespace TwoPerformant\BusinessLeagueMarketing\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Category column renderer for the commissions list
 *
 * @since 1.0.0
 */
class CategoryColumn extends Select
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value): self
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect('name');
            $collection->addFieldToFilter('level', ['eq' => 1]);
            
            $options = [];
            foreach ($collection as $category) {
                
                $label = $category->getName() . ' (ID: ' . $category->getId() . ')';
                $options[] = ['value' => $category->getId(), 'label' => $label];
            }
            $this->setOptions($options);
        }
        return parent::_toHtml();
    }
}
