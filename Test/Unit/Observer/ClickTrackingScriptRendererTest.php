<?php
namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TwoPerformant\BusinessLeagueMarketing\Observer\ClickTrackingScriptRenderer;
use Magento\Framework\View\Page\Config as PageConfig;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;
use TwoPerformant\BusinessLeagueMarketing\Model\Validator\Validator;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;

class ClickTrackingScriptRendererTest extends TestCase
{
    /** @var ClickTrackingScriptRenderer */
    private $observer;

    /** @var PageConfig|MockObject */
    private $pageConfigMock;

    /** @var Config|MockObject */
    private $configMock;

    /** @var Validator|MockObject */
    private $validatorMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    /** @var Observer|MockObject */
    private $eventObserverMock;

    protected function setUp(): void
    {
        $this->pageConfigMock = $this->createMock(PageConfig::class);
        $this->configMock = $this->createMock(Config::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->eventObserverMock = $this->createMock(Observer::class);

        $this->observer = new ClickTrackingScriptRenderer(
            $this->pageConfigMock,
            $this->configMock,
            $this->validatorMock,
            $this->loggerMock
        );
    }

    public function testExecuteAddsScriptWhenUrlIsValid()
    {
        $url = 'https://example.com/script.js';

        $this->configMock->expects($this->once())
            ->method('getClickScriptUrl')
            ->willReturn($url);
        $this->configMock->expects($this->once())
            ->method('getBigBearUnique')
            ->willReturn('TEST_ID_123');

        $this->validatorMock->expects($this->once())
            ->method('validateClickScriptUrl')
            ->with($url)
            ->willReturn(true);

        $this->pageConfigMock->expects($this->once())
            ->method('addRemotePageAsset')
            ->with(
                $url,
                'js',
                ['attributes' => ['async' => 'async']]
            );

        $this->loggerMock->expects($this->never())
            ->method('warning');

        $this->observer->execute($this->eventObserverMock);
    }

    public function testExecuteLogsWarningWhenUrlIsInvalid()
    {
        $url = 'invalid-url';

        $this->configMock->expects($this->once())
            ->method('getClickScriptUrl')
            ->willReturn($url);
        $this->configMock->expects($this->once())
            ->method('getBigBearUnique')
            ->willReturn('TEST_ID_123');

        $this->validatorMock->expects($this->once())
            ->method('validateClickScriptUrl')
            ->with($url)
            ->willReturn(false);

        $this->pageConfigMock->expects($this->never())
            ->method('addRemotePageAsset');

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'TwoPerformant: Invalid click script URL blocked.',
                ['url' => $url]
            );

        $this->observer->execute($this->eventObserverMock);
    }

    public function testExecuteLogsErrorOnException()
    {
        $exception = new \Exception('Something went wrong');

        $this->configMock->expects($this->once())
            ->method('getClickScriptUrl')
            ->willThrowException($exception);
        $this->configMock->expects($this->once())
            ->method('getBigBearUnique')
            ->willReturn('TEST_ID_123');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'TwoPerformant: Error rendering tracking script.',
                ['error' => $exception->getMessage()]
            );

        $this->observer->execute($this->eventObserverMock);
    }
}
