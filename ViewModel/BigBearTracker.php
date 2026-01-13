<?php
namespace TwoPerformant\BusinessLeagueMarketing\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

/**
 * BigBearTracker view model for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class BigBearTracker implements ArgumentInterface
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
     * Get the serialized tpOrder object
     *
     * @return string
     */
    public function getTpOrder(): string
    {
        $tpOrder = $this->transactionInfo;

        //return serialized json
        return json_encode($tpOrder, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Get the sales script URL
     *
     * @return string
     */
    public function getSlsUrl(): string
    {
        $bigBearUnique = $this->config->getBigBearUnique();
        
        // If no big bear identifier, then return an empty string
        if(empty($bigBearUnique)){
            return '';
        }
        
        return $this->config->getSalesScriptUrl();
    }
}
