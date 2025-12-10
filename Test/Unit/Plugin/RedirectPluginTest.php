<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;
use TwoPerformant\BusinessLeagueMarketing\Plugin\RedirectPlugin;

class RedirectPluginTest extends TestCase
{
    /**
     * @var RedirectPlugin
     */
    private $plugin;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var Http|MockObject
     */
    private $responseMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->responseMock = $this->createMock(Http::class);

        $this->plugin = new RedirectPlugin(
            $this->requestMock,
            $this->configMock,
            $this->urlMock
        );
    }

    /**
     * Test that no parameters are preserved when no parameters are present in the original url
     */
    public function testPreserveParamsWithNoParams()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['2pau', '2ptt', '2ptu', '2prp', '2pdlst']);
        
        $redirectUrl = 'https://example.com/checkout';
        
        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);
        
        $this->assertEquals($redirectUrl, $result[0]);
    }

    /**
     * Test that parameters are appended when redirecting to a different path
     */
    public function testAppendParamsOnPathChange()
    {
        $this->configMock->method('getBigBearParams')
            ->willReturn(['2pau', '2ptt', '2ptu']);

        $this->requestMock->method('getParam')
        ->willReturnCallback(function($param) {
            $data = [
                '2pau' => '',
                '2ptt' => 'val2',
                '2ptu' => 'val3',
            ];
            return $data[$param] ?? null;
        });

        $this->urlMock->method('getCurrentUrl')
            ->willReturn('https://example.com/checkout');

        $redirectUrl = 'https://example.com/checkout/cart';
        
        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);

        $this->assertStringContainsString('2pau=&', $result[0]);
        $this->assertStringContainsString('2ptt=val2', $result[0]);
        $this->assertStringContainsString('2ptu=val3', $result[0]);
        $this->assertStringStartsWith('https://example.com/checkout/cart', $result[0]);
    }

    /**
     * Test preserveParams with location that already has parameters
     */
    public function testPreserveParamsWithExistingLocationParams()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['2pau', '2ptt', '2ptu']);
        
        $this->requestMock->method('getParam')->willReturnCallback(function($param) {
            $data = [
                '2ptt' => 'val1',
            ];
            return $data[$param] ?? null;
        });
        
        $this->urlMock->method('getCurrentUrl')->willReturn('https://example.com/checkout');
        
        $redirectUrl = 'https://example.com/checkout/cart?param=value';
        
        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);
        
        $this->assertStringContainsString('2ptt=val1', $result[0]);
        $this->assertStringContainsString('param=value', $result[0]);
        $this->assertStringStartsWith('https://example.com/checkout/cart', $result[0]);
    }

    /**
     * Test preserveParams with URL fragments
     */
    public function testPreserveParamsWithUrlFragments()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['2ptt']);
        
        $this->requestMock->method('getParam')->willReturnCallback(function($param) {
            $data = [
                '2ptt' => 'val1',
            ];
            return $data[$param] ?? null;
        });
        
        $this->urlMock->method('getCurrentUrl')->willReturn('https://example.com/checkout#fragment');
        
        $redirectUrl = 'https://example.com/checkout/cart#fragment';
        
        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);
        
        $this->assertStringContainsString('2ptt=val1', $result[0]);
        $this->assertStringContainsString('#fragment', $result[0]);
        $this->assertStringStartsWith('https://example.com/checkout/cart', $result[0]);
    }
    /**
     * Test logic when trailing slash differs
     */
    public function testTrailingSlashDifference()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['tracking_id']);
        $this->requestMock->method('getParam')->with('tracking_id')->willReturn('123');
        $this->urlMock->method('getCurrentUrl')->willReturn('https://example.com/category');

        $redirectUrl = 'https://example.com/category/'; // Has trailing slash

        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);

        $this->assertEquals('https://example.com/category/?tracking_id=123', $result[0]);
    }

    /**
     * Test that empty parameters are handled correctly based on exclusion logic
     * If the redirect explicitly stripped a param (kept base URL same), we shouldn't re-add empty ones.
     */
    public function testEmptyParamExclusion()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['2pau']);
        
        $this->requestMock->method('getParam')->with('2pau')->willReturn('');
                
        $this->urlMock->method('getCurrentUrl')->willReturn('https://example.com/page?2pau=');
        $redirectUrl = 'https://example.com/page'; // Redirecting to self without param

        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);

        $this->assertEquals('https://example.com/page', $result[0]);
    }

    /**
     * Test loop prevention: If adding params creates the EXACT same URL as current, don't change it.
     */
    public function testLoopPrevention()
    {
        $this->configMock->method('getBigBearParams')->willReturn(['2pau']);
        $this->requestMock->method('getParam')->with('2pau')->willReturn('123');
        
        $currentUrl = 'https://example.com/page?2pau=123';
        $this->urlMock->method('getCurrentUrl')->willReturn($currentUrl);

        $redirectUrl = 'https://example.com/page?2pau=123';

        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);

        $this->assertEquals($redirectUrl, $result[0]);
    }

    /**
     * Test no params configured
     */
    public function testNoParamsConfigured()
    {
        $this->configMock->method('getBigBearParams')->willReturn([]);
        $redirectUrl = 'https://example.com/target';
        
        $result = $this->plugin->beforeSetRedirect($this->responseMock, $redirectUrl);
        
        $this->assertEquals($redirectUrl, $result[0]);
    }
}
