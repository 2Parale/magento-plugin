<?php
namespace TwoPerformant\BusinessLeagueMarketing\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Psr\Log\LoggerInterface;

use TwoPerformant\BusinessLeagueMarketing\Model\Config;
use TwoPerformant\BusinessLeagueMarketing\Model\Validator\Validator;

/**
 * Observer for rendering the tracking scripts
 *
 * @since 1.0.0
 */
class ClickTrackingScriptRenderer implements ObserverInterface
{
    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param PageConfig $pageConfig
     * @param Config $config
     * @param Validator $validator
     * @param LoggerInterface $logger
     */
    public function __construct(PageConfig $pageConfig, Config $config, Validator $validator, LoggerInterface $logger)
    {
        $this->pageConfig = $pageConfig;
        $this->config = $config;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Execute the observer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $bigBearUnique = $this->config->getBigBearUnique();

        // If no big bear identifier, then don't add the click script
        if(empty($bigBearUnique)){
            return;
        }

        try {

            // Get the click script URL
            $scriptUrl = $this->config->getClickScriptUrl();

            // Validate the click script URL
            if ($this->validator->validateClickScriptUrl($scriptUrl)) {
                // Add the click script to the page
                $this->pageConfig->addRemotePageAsset(
                    $scriptUrl,
                    'js',
                    ['attributes' => ['async' => 'async']]
                );
            } else {
                // Log warning that script is invalid
                $this->logger->warning(
                    'TwoPerformant: Invalid click script URL blocked.',
                    ['url' => $scriptUrl]
                );
            }
        } catch (\Exception $e) {
            // Log error that there was an error rendering the tracking script but don't crash the page
            $this->logger->error('TwoPerformant: Error rendering tracking script.', ['error' => $e->getMessage()]);
        }
    }
}
