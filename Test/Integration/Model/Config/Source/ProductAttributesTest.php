<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\Config\Source\ProductAttributes;

class ProductAttributesTest extends TestCase
{
    public function testToOptionArrayReturnsVisibleAttributes(): void
    {
        $source = Bootstrap::getObjectManager()->get(ProductAttributes::class);
        $options = $source->toOptionArray();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
        $this->assertSame('-- Please Select --', (string) $options[0]['label']);

        $hasRealOption = false;
        foreach ($options as $option) {
            if (!empty($option['value']) && !empty($option['label'])) {
                $hasRealOption = true;
                break;
            }
        }
        $this->assertTrue($hasRealOption);
    }
}