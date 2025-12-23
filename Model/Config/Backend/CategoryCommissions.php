<?php
namespace TwoPerformant\BusinessLeagueMarketing\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Exception\ValidatorException;

/**
 * Category commissions backend model to validate the category commissions data
 *
 * @since 1.0.0
 */
class CategoryCommissions extends ArraySerialized
{
    /**
     * Validate data before saving
     *
     * @return $this
     * @throws ValidatorException
     */
    public function beforeSave(): self
    {
        // Retrieve the array data being saved
        $values = $this->getValue();

        if (is_array($values)) {
            foreach ($values as $rowId => $row) {
                // Skip the special "__empty" row used by Magento's UI
                if ($rowId === '__empty') {
                    continue;
                }

                // Validate Commission Value
                if (!isset($row['commission_value']) || !is_numeric($row['commission_value'])) {
                    throw new ValidatorException(
                        __('Commission value must be a number.')
                    );
                }

                if ($row['commission_value'] < 0) {
                    throw new ValidatorException(
                        __('Commission value cannot be negative.')
                    );
                }

                // Validate Category ID (Optional but recommended)
                if (empty($row['category_id'])) {
                    throw new ValidatorException(
                        __('A category must be selected for all rows.')
                    );
                }
            }
        }

        // Proceed with standard serialization
        return parent::beforeSave();
    }
}
