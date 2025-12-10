<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\BigBearTracker;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

class BigBearTrackerTest extends TestCase
{
    /**
     * @var TransactionInfo|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionInfoMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->transactionInfoMock = $this->createMock(TransactionInfo::class);
        $this->configMock = $this->createMock(Config::class);
    }

    public function testGetTpOrderReturnsJsonEncodedTransactionInfo()
    {
        $transactionInfoData = [
            'id' => '10001',
            'items' => [
                ['product_id' => '1', 'name' => 'Test Product']
            ]
        ];

        // Configure mock
        $this->transactionInfoMock->method('getTransactionInfo')->willReturn($transactionInfoData);

        // Instantiate
        $bigBearTracker = new BigBearTracker($this->transactionInfoMock, $this->configMock);

        $expectedJson = json_encode(
            $transactionInfoData,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );

        $this->assertEquals($expectedJson, $bigBearTracker->getTpOrder());
    }

    public function testGetSlsUrlReturnsUrlFromConfig()
    {
        // Transaction info is not used in getSlsUrl, but constructor calls it.
        $this->transactionInfoMock->method('getTransactionInfo')->willReturn([]);
        
        $expectedUrl = 'https://example.com/sales-script';
        $this->configMock->method('getSalesScriptUrl')->willReturn($expectedUrl);

        // Instantiate
        $bigBearTracker = new BigBearTracker($this->transactionInfoMock, $this->configMock);

        $this->assertEquals($expectedUrl, $bigBearTracker->getSlsUrl());
    }
}
