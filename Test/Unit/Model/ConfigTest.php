<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

class ConfigTest extends TestCase
{
    private const PATH_CAMPAIGN_UNIQUE = 'twoperformant_identifiers/identifiers/campaign_unique';
    private const PATH_CONFIRM = 'twoperformant_identifiers/identifiers/confirm';
    private const PATH_BIG_BEAR_UNIQUE = 'twoperformant_identifiers/identifiers/big_bear_unique';
    private const PATH_BRAND_ATTRIBUTE_NAME = 'twoperformant_identifiers/store_specific/brand_attribute_name';
    private const PATH_BIG_BEAR_PARAMS = 'twoperformant/params/big_bear_params';
    private const PATH_IFRAME_URL = 'twoperformant/urls/iframe_url';
    private const PATH_CLICK_SCRIPT_URL = 'twoperformant/urls/click_script_url';
    private const PATH_SALES_SCRIPT_URL = 'twoperformant/urls/sales_script_url';
    private const PATH_DEFAULT_COMMISSION_VALUE = 'twoperformant_commissions/commissions/category_commissions_default_commission';
    private const PATH_CATEGORY_COMMISSIONS_ENABLED = 'twoperformant_commissions/commissions/category_commissions_enabled';
    private const PATH_CATEGORY_COMMISSIONS = 'twoperformant_commissions/commissions/category_commissions';

    private function buildConfigWithMap(array $map): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static function (string $path) use ($map) {
                return $map[$path] ?? null;
            }
        );

        return new Config($scopeConfig);
    }

    public function testGetBigBearParamsReturnsArray(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_BIG_BEAR_PARAMS => '{"foo":"bar","count":2}',
        ]);

        $this->assertSame(['foo' => 'bar', 'count' => 2], $config->getBigBearParams());
    }

    public function testGetClickScriptUrlReplacesPlaceholder(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_CLICK_SCRIPT_URL => 'https://example.com/click/__replace_me__.js',
            self::PATH_BIG_BEAR_UNIQUE => 'ABC123',
        ]);

        $this->assertSame(
            'https://example.com/click/ABC123.js',
            $config->getClickScriptUrl()
        );
    }

    public function testGetSalesScriptUrlReplacesPlaceholder(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_SALES_SCRIPT_URL => 'https://example.com/sales/__replace_me__.js',
            self::PATH_BIG_BEAR_UNIQUE => 'XYZ789',
        ]);

        $this->assertSame(
            'https://example.com/sales/XYZ789.js',
            $config->getSalesScriptUrl()
        );
    }

    public function testGetCategoryCommissionsParsesRows(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_CATEGORY_COMMISSIONS => '[{"category_id":"12","commission_value":"25.5"},{"category_id":34,"commission_value":10}]',
        ]);

        $this->assertSame(
            [12 => 25.5, 34 => 10.0],
            $config->getCategoryCommissions()
        );
    }

    public function testGetCategoryCommissionsReturnsEmptyForInvalidJson(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_CATEGORY_COMMISSIONS => 'not-json',
        ]);

        $this->assertSame([], $config->getCategoryCommissions());
    }

    public function testGetCategoryCommissionsEnabledPassesThroughValue(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_CATEGORY_COMMISSIONS_ENABLED => true,
        ]);

        $this->assertTrue($config->getCategoryCommissionsEnabled());
    }

    public function testGetDefaultCommissionValuePassesThroughValue(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_DEFAULT_COMMISSION_VALUE => 7.5,
        ]);

        $this->assertSame(7.5, $config->getDefaultCommissionValue());
    }

    public function testSimpleGettersPassThroughValues(): void
    {
        $config = $this->buildConfigWithMap([
            self::PATH_CAMPAIGN_UNIQUE => 'campaign-1',
            self::PATH_CONFIRM => 'confirm-1',
            self::PATH_BRAND_ATTRIBUTE_NAME => 'brand_attr',
            self::PATH_BIG_BEAR_UNIQUE => 'bigbear-1',
            self::PATH_IFRAME_URL => 'https://example.com/iframe',
        ]);

        $this->assertSame('campaign-1', $config->getCampaignUnique());
        $this->assertSame('confirm-1', $config->getConfirm());
        $this->assertSame('brand_attr', $config->getBrandAttributeName());
        $this->assertSame('bigbear-1', $config->getBigBearUnique());
        $this->assertSame('https://example.com/iframe', $config->getIframeUrl());
    }
}