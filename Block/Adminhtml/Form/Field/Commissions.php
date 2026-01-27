<?php
namespace TwoPerformant\BusinessLeagueMarketing\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

use TwoPerformant\BusinessLeagueMarketing\Block\Adminhtml\Form\Field\CategoryColumn;

/**
 * Commissions block for the category commissions list
 *
 * @since 1.0.0
 */
class Commissions extends AbstractFieldArray
{
    /**
     * @var CategoryColumn
     */
    private $categoryRenderer;

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('category_id', [
            'label' => __('Category'),
            'renderer' => $this->getCategoryRenderer()
        ]);
        
        $this->addColumn('commission_value', [
            'label' => __('Commission'),
            'style' => 'width:100px',
            'class' => 'required-entry validate-number'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Commission');
    }

    /**
     * Retrieve category column renderer
     *
     * @return CategoryColumn
     */
    private function getCategoryRenderer(): CategoryColumn
    {
        if (!$this->categoryRenderer) {
            $this->categoryRenderer = $this->getLayout()->createBlock(
                CategoryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->categoryRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $categoryRenderer = $this->getCategoryRenderer();
        
        // This ensures the correct option is selected when loading saved data
        $key = 'option_' . $categoryRenderer->calcOptionHash($row->getData('category_id'));
        $options[$key] = 'selected="selected"';
        
        $row->setData('option_extra_attrs', $options);
    }
}
