<?php
namespace TwoPerformant\BusinessLeagueMarketing\Model\Validator;

/**
 * Validator for the BusinessLeagueMarketing module
 *
 * @since 1.0.0
 */
class Validator
{
    /**
     * Allowed domains for the click script URL
     *
     * @var array
     */
    private const ALLOWED_DOMAINS = ['attr-2p.com'];

    /**
     * Allowed path for the click script
     *
     * @var string
     */
    private const CLICK_SCRIPT_PATH = '/clc/1.js';

    /**
     * Validate the BusinessLeagueMarketing module
     *
     * @param string $url
     * @return bool
     */
    public function validateClickScriptUrl(string $url): bool
    {
        // Validate the URL
        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($url);
            
            // Validate the scheme, host, and path
            if (isset($parsedUrl['scheme'], $parsedUrl['host']) &&
                $parsedUrl['scheme'] === 'https' &&
                in_array($parsedUrl['host'], self::ALLOWED_DOMAINS) &&
                strpos($parsedUrl['path'], self::CLICK_SCRIPT_PATH) !== false
            ) {
                return true;
            }
        }
        return false;
    }
}
