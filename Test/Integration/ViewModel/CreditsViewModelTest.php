<?php
declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration\ViewModel;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\ViewModel\CreditsViewModel;

class CreditsViewModelTest extends TestCase
{
    /**
     * @var CreditsViewModel
     */
    private $viewModel;

    protected function setUp(): void
    {
        $this->viewModel = Bootstrap::getObjectManager()->get(CreditsViewModel::class);
    }

    public function testViewModelCanBeInstantiatedViaDi(): void
    {
        $this->assertInstanceOf(CreditsViewModel::class, $this->viewModel);
    }

    /**
     * Default values from config.xml should be loaded when no overrides exist.
     */
    public function testGettersReturnDefaultConfigValues(): void
    {
        $this->assertSame('Active in', $this->viewModel->getText());
        $this->assertSame('BusinessLeague', $this->viewModel->getUrlText());
        $this->assertSame('https://businessleague.com/', $this->viewModel->getUrl());
    }
}
