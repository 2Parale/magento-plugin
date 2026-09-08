<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\Block\Adminhtml\Form\Field;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Block\Adminhtml\Form\Field\CategoryColumn;

class CategoryColumnTest extends TestCase
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
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testToHtmlBuildsOptionsFromCategories(): void
    {
        $block = $this->layout->createBlock(CategoryColumn::class);
        $html = $block->toHtml();
        $options = $block->getOptions();

        $this->assertNotEmpty($html);
        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        $first = reset($options);
        $this->assertArrayHasKey('value', $first);
        $this->assertArrayHasKey('label', $first);
        $this->assertStringContainsString('(ID:', $first['label']);
        $this->assertStringContainsString('option', $html);

        // "Default Category" (ID: 2) is level 1 and must be excluded now
        foreach ($options as $option) {
            $this->assertNotEquals(2, $option['value']);
        }
    }
}
