<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Observer\ClickTrackingScriptRenderer;

class ClickTrackingScriptRendererTest extends TestCase
{
    private function getAssetUrls(PageConfig $pageConfig): array
    {
        $urls = [];
        foreach ($pageConfig->getAssetCollection()->getAll() as $asset) {
            if (method_exists($asset, 'getUrl')) {
                $urls[] = $asset->getUrl();
            }
        }
        return $urls;
    }

    /**
     * @magentoAppArea frontend
     */
    public function testExecuteSkipsWhenBigBearUniqueMissing(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $renderer = $objectManager->get(ClickTrackingScriptRenderer::class);
        $pageConfig = $objectManager->get(PageConfig::class);

        $renderer->execute(new Observer());

        $urls = $this->getAssetUrls($pageConfig);
        $matches = array_filter($urls, static function ($url) {
            return strpos($url, '/clc/1.js') !== false;
        });

        $this->assertEmpty($matches);
    }

     /**
     * @magentoAppArea frontend
     * @magentoConfigFixture default/twoperformant_identifiers/identifiers/big_bear_unique TEST_ID_123
     */
    public function testExecuteAddsClickScriptWhenValid(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $renderer = $objectManager->get(ClickTrackingScriptRenderer::class);
        $pageConfig = $objectManager->get(PageConfig::class);

        $renderer->execute(new Observer());

        $urls = $this->getAssetUrls($pageConfig);
        $matches = array_filter($urls, static function ($url) {
            return strpos($url, '/TEST_ID_123/clc/1.js') !== false;
        });

        $this->assertNotEmpty($matches);
    }
}
