<?php

declare(strict_types=1);

namespace TwoPerformant\BusinessLeagueMarketing\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ViewModel for the footer credits block
 *
 * @since 1.1.0
 */
class CreditsViewModel implements ArgumentInterface
{
    private const CONFIG_PATH_PREFIX = 'twoperformant_credits/components/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns the credits text label from config.
     *
     * @return string
     */
    public function getText(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'text',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the credits URL anchor text from config.
     *
     * @return string
     */
    public function getUrlText(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'url_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the credits URL from config.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'url',
            ScopeInterface::SCOPE_STORE
        );
    }
}
