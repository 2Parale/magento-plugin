<?php

namespace TwoPerformant\BusinessLeagueMarketing\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

/**
 * PixelTracker view model for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class PixelTracker implements ArgumentInterface
{

    /**
     * @var array|null
     */
    protected $transactionInfo;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param TransactionInfo $transactionInfo
     * @param Config $config
     */
    public function __construct(TransactionInfo $transactionInfo, Config $config)
    {
        $this->transactionInfo = $transactionInfo->getTransactionInfo();
        $this->config = $config;
    }

    /**
     * Generates the full iframe URL to track the current order
     *
     * @return string
     */
    public function getIframeUrl(): string
    {
        
        // get the iframe base URL and the pixel identifiers from the config
        $iframeBaseUrl = $this->config->getIframeUrl();
        $campaignUnique = $this->config->getCampaignUnique();
        $confirm = $this->config->getConfirm();
        
        // If no campaign unique or confirm identifier, then return an empty string
        if (empty($campaignUnique) || empty($confirm)) {
            return '';
        }
        
        // If no transaction info, then return an empty string
        if (empty($this->transactionInfo)) {
            return '';
        }

        // initialize the total value and the array that will hold the name of the items in the order
        $totalValue = 0;
        $descriptionParts = [];
        $commissionPercentage = 0;

        // loop through the order items and calculate the total value and the description
        foreach ($this->transactionInfo['items'] as $item) {
            $value = (float)$item['value'] * (int)$item['quantity'];
            $totalValue += $value;
            $descriptionParts[] = (string)$item['name'];
            
            if ($this->config->getCategoryCommissionsEnabled()) {
                $commissionPercentage += $value * (float)$item['commission_percent'];
            }
        }

        // generate the description string
        $description = implode(', ', $descriptionParts);

        // construct the query arguments array
        $queryArgs = [
            'campaign_unique' => $campaignUnique,
            'confirm' => $confirm,
            'amount' => number_format((float)$totalValue, 2, '.', ''), //format the total value as a float with 2 decimal places
            'description' => $description,
            'transaction_id' => $this->transactionInfo['id']
        ];

        if ($this->config->getCategoryCommissionsEnabled() && $totalValue > 0) {
            $commissionPercentage = $commissionPercentage / $totalValue;
            $queryArgs['com_percent'] = number_format((float)$commissionPercentage, 2, '.', '');
        }

        // generate the query string
        $queryString = http_build_query($queryArgs);

        // return the full iframe URL
        return $iframeBaseUrl . '?' . $queryString;
    }
}
