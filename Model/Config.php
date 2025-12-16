<?php

namespace TwoPerformant\BusinessLeagueMarketing\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Config model for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class Config
{
    /**
     * Define where the config values are stored in the Magento configuration
     *
     * @var string
     */
    private const PATH_CAMPAIGN_UNIQUE = 'twoperformant/identifiers/campaign_unique';
    private const PATH_CONFIRM = 'twoperformant/identifiers/confirm';
    private const PATH_BIG_BEAR_UNIQUE = 'twoperformant/identifiers/big_bear_unique';
    private const PATH_BIG_BEAR_PARAMS = 'twoperformant/params/big_bear_params';
    private const PATH_IFRAME_URL = 'twoperformant/urls/iframe_url';
    private const PATH_CLICK_SCRIPT_URL = 'twoperformant/urls/click_script_url';
    private const PATH_SALES_SCRIPT_URL = 'twoperformant/urls/sales_script_url';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the campaign unique identifier
     *
     * @return string
     */
    public function getCampaignUnique(): string
    {
        
        return $this->scopeConfig->getValue(self::PATH_CAMPAIGN_UNIQUE);
    }

    /**
     * Get the confirm identifier
     *
     * @return string
     */
    public function getConfirm(): string
    {
        return $this->scopeConfig->getValue(self::PATH_CONFIRM);
    }

    /**
     * Get the big bear unique identifier
     *
     * @return string
     */
    public function getBigBearUnique(): string
    {
        return $this->scopeConfig->getValue(self::PATH_BIG_BEAR_UNIQUE);
    }
    
    /**
     * Get the big bear parameters
     *
     * @return array
     */
    public function getBigBearParams(): array
    {
        $paramsString = $this->scopeConfig->getValue(self::PATH_BIG_BEAR_PARAMS);
        $paramsArray = json_decode($paramsString, true);
        return $paramsArray;
    }

    /**
     * Get the iframe URL
     *
     * @return string
     */
    public function getIframeUrl(): string
    {
        return $this->scopeConfig->getValue(self::PATH_IFRAME_URL);
    }

    /**
     * Get the click script URL
     *
     * @return string
     */
    public function getClickScriptUrl(): string
    {
        $clickUrlPattern = $this->scopeConfig->getValue(self::PATH_CLICK_SCRIPT_URL);
        $bigBearUnique = $this->getBigBearUnique();
        $clickUrl = str_replace('__replace_me__', $bigBearUnique, $clickUrlPattern);
        return $clickUrl;
    }

    /**
     * Get the sales script URL
     *
     * @return string
     */
    public function getSalesScriptUrl(): string
    {
        $salesScriptUrlPattern = $this->scopeConfig->getValue(self::PATH_SALES_SCRIPT_URL);
        $bigBearUnique = $this->getBigBearUnique();
        $salesScriptUrl = str_replace('__replace_me__', $bigBearUnique, $salesScriptUrlPattern);
        return $salesScriptUrl;
    }
}
