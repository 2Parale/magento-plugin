<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\Block\Adminhtml\Form\Field;

use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Block\Adminhtml\Form\Field\Commissions;

class CommissionsTest extends TestCase
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    protected function setUp(): void
    {
        $this->layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testPrepareToRenderDefinesColumns(): void
    {
        $block = $this->layout->createBlock(Commissions::class);

        $method = new \ReflectionMethod($block, '_prepareToRender');
        $method->setAccessible(true);
        $method->invoke($block);

        $columnsProp = new \ReflectionProperty($block, '_columns');
        $columnsProp->setAccessible(true);
        $columns = $columnsProp->getValue($block);

        $this->assertArrayHasKey('category_id', $columns);
        $this->assertArrayHasKey('commission_value', $columns);
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testPrepareArrayRowSetsSelectedOption(): void
    {
        $block = $this->layout->createBlock(Commissions::class);

        $method = new \ReflectionMethod($block, '_prepareArrayRow');
        $method->setAccessible(true);

        $row = new DataObject(['category_id' => 1]);
        $method->invoke($block, $row);

        $extra = $row->getData('option_extra_attrs');
        $this->assertIsArray($extra);
        $this->assertNotEmpty($extra);
        $this->assertStringContainsString('selected="selected"', implode(' ', $extra));
    }
}
