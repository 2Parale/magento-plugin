<?php
namespace TwoPerformant\BusinessLeagueMarketing\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use TwoPerformant\BusinessLeagueMarketing\Model\TransactionInfo;

/**
 * TpOrder view model for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class TpOrder implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var TransactionInfo
     */
    protected $transactionInfo;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param TransactionInfo $transactionInfo
     */
    public function __construct(CheckoutSession $checkoutSession, TransactionInfo $transactionInfo)
    {
        $this->checkoutSession = $checkoutSession;
        $this->transactionInfo = $transactionInfo;
    }

    /**
     * Get the serialized tpOrder object
     *
     * @return string
     */
    public function getTpOrder(): string
    {
        $tpOrder = $this->transactionInfo->getTransactionInfo();

        //return serialized json
        return json_encode($tpOrder);
    }
}
