<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\CreditsViewModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CreditsViewModelTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CreditsViewModel
     */
    private $creditsViewModel;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->creditsViewModel = new CreditsViewModel($this->scopeConfigMock);
    }

    public function testGetTextReturnsConfigValue()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/text', ScopeInterface::SCOPE_STORE)
            ->willReturn('Active in');

        $this->assertEquals('Active in', $this->creditsViewModel->getText());
    }

    public function testGetUrlTextReturnsConfigValue()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/url_text', ScopeInterface::SCOPE_STORE)
            ->willReturn('BusinessLeague');

        $this->assertEquals('BusinessLeague', $this->creditsViewModel->getUrlText());
    }

    public function testGetUrlReturnsConfigValue()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/url', ScopeInterface::SCOPE_STORE)
            ->willReturn('https://businessleague.com/');

        $this->assertEquals('https://businessleague.com/', $this->creditsViewModel->getUrl());
    }

    public function testGetTextReturnsEmptyStringWhenConfigIsNull()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/text', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->creditsViewModel->getText());
    }

    public function testGetUrlTextReturnsEmptyStringWhenConfigIsNull()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/url_text', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->creditsViewModel->getUrlText());
    }

    public function testGetUrlReturnsEmptyStringWhenConfigIsNull()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('twoperformant_credits/components/url', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->assertSame('', $this->creditsViewModel->getUrl());
    }

    /**
     * @dataProvider allGettersDataProvider
     */
    public function testAllGettersUseStoreScope(string $method, string $expectedConfigSuffix)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'twoperformant_credits/components/' . $expectedConfigSuffix,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn('test');

        $this->creditsViewModel->$method();
    }

    public function allGettersDataProvider(): array
    {
        return [
            'getText uses text config path'       => ['getText', 'text'],
            'getUrlText uses url_text config path' => ['getUrlText', 'url_text'],
            'getUrl uses url config path'          => ['getUrl', 'url'],
        ];
    }
}
