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
     * @return string|null
     */
    public function getCampaignUnique(): ?string
    {
        
        return $this->scopeConfig->getValue(self::PATH_CAMPAIGN_UNIQUE);
    }

    /**
     * Get the confirm identifier
     *
     * @return string|null
     */
    public function getConfirm(): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_CONFIRM);
    }

    /**
     * Get the brand attribute name
     *
     * @return string|null
     */
    public function getBrandAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_BRAND_ATTRIBUTE_NAME);
    }

    /**
     * Get the big bear unique identifier
     *
     * @return string|null
     */
    public function getBigBearUnique(): ?string
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

    /**
     * Get the default commission value
     *
     * @return float
     */
    public function getDefaultCommissionValue(): float
    {
        return $this->scopeConfig->getValue(self::PATH_DEFAULT_COMMISSION_VALUE);
    }

    /**
     * Get the category commissions enabled
     *
     * @return bool
     */
    public function getCategoryCommissionsEnabled(): bool
    {
        return $this->scopeConfig->getValue(self::PATH_CATEGORY_COMMISSIONS_ENABLED);
    }

    /**
     * Get the category commissions
     *
     * @return array
     */
    public function getCategoryCommissions(): array
    {
        $categoryCommissionsString = $this->scopeConfig->getValue(self::PATH_CATEGORY_COMMISSIONS);
        $categoryCommissionsArray = json_decode($categoryCommissionsString, true);
        
        // Handle null/empty case
        if (!is_array($categoryCommissionsArray)) {
            return [];
        }
        
        // Build a flat array: category_id => commission_value
        $result = [];
        foreach ($categoryCommissionsArray as $row) {
            if (isset($row['category_id']) && isset($row['commission_value'])) {
                // Convert commission_value to float/int if needed
                $result[(int)$row['category_id']] = (float)$row['commission_value'];
            }
        }
        
        return $result;
    }
}
