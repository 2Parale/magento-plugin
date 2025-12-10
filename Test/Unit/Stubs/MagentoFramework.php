<?php

// We use bracketed namespaces to define multiple namespaces in one file.
// This file should ONLY be loaded if the real framework is missing.

namespace Magento\Framework\App {
    if (!interface_exists('Magento\Framework\App\RequestInterface')) {
        interface RequestInterface {
            public function getParam($key, $defaultValue = null);
            public function getParams();
        }
    }
}

namespace Magento\Framework {
    if (!interface_exists('Magento\Framework\UrlInterface')) {
        interface UrlInterface {
            public function getCurrentUrl();
        }
    }
}

namespace Magento\Framework\App\Response {
    if (!class_exists('Magento\Framework\App\Response\Http')) {
        class Http {
            public function setRedirect($url, $code = 302) {}
        }
    }
}
