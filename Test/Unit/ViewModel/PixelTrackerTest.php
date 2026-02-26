<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\PixelTracker;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

class PixelTrackerTest extends TestCase
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

    public function testGetIframeUrlReturnsEmptyStringWhenTransactionInfoIsEmpty()
    {
        // Configure mock to return null (empty transaction info)
        $this->transactionInfoMock->method('getTransactionInfo')->willReturn(null);

        // Instantiate with the configured mock
        $pixelTracker = new PixelTracker($this->transactionInfoMock, $this->configMock);

        $this->assertEquals('', $pixelTracker->getIframeUrl());
    }

    public function testGetIframeUrlReturnsCorrectUrlWithItems()
    {
        // Mock transaction info data
        $items = [
            [
                'name' => 'Product A',
                'value' => 100.00,
                'quantity' => 2,
                'commission_percent' => 10.00
            ],
            [
                'name' => 'Product B',
                'value' => 50.50,
                'quantity' => 1,
                'commission_percent' => 10.00
            ]
        ];

        $transactionInfoData = [
            'id' => '10000001',
            'items' => $items
        ];

        // Configure mocks
        $this->transactionInfoMock->method('getTransactionInfo')->willReturn($transactionInfoData);

        $this->configMock->method('getIframeUrl')->willReturn('https://example.com/iframe');
        $this->configMock->method('getCampaignUnique')->willReturn('unique_campaign_id');
        $this->configMock->method('getConfirm')->willReturn('confirm_code');
        $this->configMock->method('getCategoryCommissionsEnabled')->willReturn(true);

        // Instantiate
        $pixelTracker = new PixelTracker($this->transactionInfoMock, $this->configMock);

        // Calculate expected values
        // Item 1: 100 * 2 = 200
        // Item 2: 50.50 * 1 = 50.50
        // Total: 250.50
        $expectedTotalValue = number_format(250.50, 2, '.', '');
        $expectedDescription = 'Product A, Product B';
        $expectedCommissionPercentage = number_format(10, 2, '.', '');

        $expectedQuery = http_build_query([
            'campaign_unique' => 'unique_campaign_id',
            'confirm' => 'confirm_code',
            'amount' => $expectedTotalValue,
            'description' => $expectedDescription,
            'transaction_id' => '10000001',
            'com_percent' => $expectedCommissionPercentage
        ]);

        $expectedUrl = 'https://example.com/iframe?' . $expectedQuery;

        $this->assertEquals($expectedUrl, $pixelTracker->getIframeUrl());
    }
}
