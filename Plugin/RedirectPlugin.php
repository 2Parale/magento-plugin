<?php

namespace TwoPerformant\BusinessLeagueMarketing\Plugin;

use Magento\Framework\App\Response\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use TwoPerformant\BusinessLeagueMarketing\Model\Config;

/**
 * Intercepts redirects to preserve tracking parameters
 *
 * @since 1.0.0
 */
class RedirectPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param RequestInterface $request
     * @param Config $config
     * @param UrlInterface $url
     */
    public function __construct(RequestInterface $request, Config $config,UrlInterface $url)
    {
        $this->request = $request;
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * Before setRedirect plugin
     *
     * @param Http $subject
     * @param string $url
     * @param int $code
     * @return array
     */
    public function beforeSetRedirect(Http $subject, $url, $code = 302)
    {
        $newUrl = $this->preserveParams($url);
        return [$newUrl, $code];
    }

    /**
     * Preserve parameters during redirects
     *
     * @param string $location Redirect location
     * @return string Modified location
     */
    private function preserveParams($location)
    {
        // Get BigBear params from request
        $paramsToAdd = [];
        $emptyParams = [];
        $nonEmptyParams = [];
        $bigBearParams = $this->config->getBigBearParams();

        if (empty($bigBearParams)) {
            return $location;
        }

        foreach ($bigBearParams as $param) {
            $value = $this->request->getParam($param);
            if ($value !== null) {
                
                $paramsToAdd[$param] = $value;

                if ($value !== '') {
                    $nonEmptyParams[$param] = $value;
                } else {
                    $emptyParams[] = $param;
                }
            }
        }

        // If no params, return original location
        if (empty($paramsToAdd)) {
            return $location;
        }

        // Get location params
        $parsedUrl = parse_url($location);
        $locationParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $locationParams);
        }

        // Process redirect
        if ($this->isEmptyParamExclusionRedirect($location, $emptyParams, $locationParams)) {
            $newUrl = $this->appendNonEmptyParams($location, $nonEmptyParams, $locationParams);
        } else {
            $newUrl = $this->appendAllParams($location, $paramsToAdd);
        }

        // Prevent redirect loops
        if ($newUrl === $location) {
            return $location;
        }

        // Extra loop prevention
        if ($this->normalizeUrl($newUrl) === $this->normalizeUrl($this->url->getCurrentUrl())) {
            return $location;
        }

        return $newUrl;
    }

    /**
     * Check if this is an empty parameter exclusion redirect
     *
     * @param string $location
     * @param array $emptyParams
     * @param array $locationParams
     * @return bool
     */
    private function isEmptyParamExclusionRedirect($location, $emptyParams, $locationParams)
    {
        $parsedLocation = parse_url($location);
        $parsedCurrentUrl = parse_url($this->url->getCurrentUrl());

        // Compare all parts except query
        foreach ($parsedLocation as $key => $value) {
            if ($key === 'query') {
                continue;
            }
            // If scheme/host/path differ, it's not the same base URL
            if (!isset($parsedCurrentUrl[$key]) || $parsedLocation[$key] !== $parsedCurrentUrl[$key]) {
                return false; 
            }
        }

        // Check if empty parameters are excluded
        foreach ($emptyParams as $param) {
            if (!isset($locationParams[$param])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Append non-empty parameters to location
     *
     * @param string $location
     * @param array $params
     * @param array $locationParams
     * @return string
     */
    private function appendNonEmptyParams($location, $params, $locationParams)
    {
        $parsedUrl = parse_url($location);

        // Merge parameters
        $added = false;
        foreach ($params as $param => $value) {
            if (!isset($locationParams[$param])) {
                $locationParams[$param] = $value;
                $added = true;
            }
        }

        if (!$added) {
            return $location;
        }

        return $this->buildUrl($parsedUrl, $locationParams);
    }

    /**
     * Append all parameters
     *
     * @param string $location
     * @param array $params
     * @return string
     */
    private function appendAllParams($location, $params)
    {
        $parsedUrl = parse_url($location);
        $existingParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $existingParams);
        }

        // Merge parameters
        $mergedParams = array_merge($existingParams, $params);

        return $this->buildUrl($parsedUrl, $mergedParams);
    }

    /**
     * Build URL from parts
     *
     * @param array $urlParts
     * @param array $queryParams
     * @return string
     */
    private function buildUrl($urlParts, $queryParams)
    {
        $url = '';

        // Add scheme and host
        if (isset($urlParts['scheme']) && isset($urlParts['host'])) {
            $url .= $urlParts['scheme'] . '://' . $urlParts['host'];
        }

        // Add port
        if (isset($urlParts['port'])) {
            $url .= ':' . $urlParts['port'];
        }

        // Add path
        if (isset($urlParts['path'])) {
            $url .= $urlParts['path'];
        }

        // Add query
        $query = http_build_query($queryParams);
        if (!empty($query)) {
            $url .= '?' . $query;
        }

        // Add fragment
        if (isset($urlParts['fragment'])) {
            $url .= '#' . $urlParts['fragment'];
        }

        return $url;
    }

    /**
     * Normalize URL for comparison
     *
     * @param string $url
     * @return string
     */
    private function normalizeUrl($url)
    {
        $parts = parse_url($url);
        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Remove BigBear params
        foreach ($this->config->getBigBearParams() as $param) {
            unset($query[$param]);
        }

        // Sort params
        ksort($query);

        // Build normalized URL
        $normalized = '';

        if (isset($parts['scheme']) && isset($parts['host'])) {
            $normalized .= $parts['scheme'] . '://' . $parts['host'];
        }

        if (isset($parts['path'])) {
            $normalized .= $parts['path'];
        }

        $queryString = http_build_query($query);
        if (!empty($queryString)) {
            $normalized .= '?' . $queryString;
        }

        return $normalized;
    }
}
