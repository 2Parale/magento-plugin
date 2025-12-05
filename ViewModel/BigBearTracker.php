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
     * @param CheckoutSession $checkoutSession
     * @param TransactionInfo $transactionInfo
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
        return json_encode($tpOrder);
    }

    public function getSlsUrl(): string
    {
        return $this->config->getSalesScriptUrl();
    }
}
