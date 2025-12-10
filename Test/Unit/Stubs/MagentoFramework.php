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

namespace Magento\Checkout\Model {
    if (!class_exists('Magento\Checkout\Model\Session')) {
        class Session {
            public function getLastRealOrder() {}
        }
    }
}

namespace Magento\Sales\Model {
    if (!class_exists('Magento\Sales\Model\Order')) {
        class Order {
            public function getIncrementId() {}
            public function getCreatedAt() {}
            public function getOrderCurrencyCode() {}
            public function getItems() {}
        }
    }
}

namespace Magento\Sales\Model\Order {
    if (!class_exists('Magento\Sales\Model\Order\Item')) {
        class Item {
            public function getPrice() {}
            public function getProduct() {}
            public function getProductId() {}
            public function getName() {}
            public function getQtyOrdered() {}
        }
    }
}

namespace Magento\Catalog\Model {
    if (!class_exists('Magento\Catalog\Model\Product')) {
        class Product {
            public function getCategoryCollection() {}
            public function getResource() {}
            public function getAttributeText($attributeCode) {}
            public function getData($key = '', $index = null) {}
        }
    }

    if (!class_exists('Magento\Catalog\Model\Category')) {
        class Category {
            public function getName() {}
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel {
    if (!class_exists('Magento\Catalog\Model\ResourceModel\Product')) {
        class Product {
            public function getAttribute($attribute) {}
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel\Eav {
    if (!class_exists('Magento\Catalog\Model\ResourceModel\Eav\Attribute')) {
        class Attribute {
            public function usesSource() {}
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel\Category {
    if (!class_exists('Magento\Catalog\Model\ResourceModel\Category\Collection')) {
        // Must implement IteratorAggregate to allow iteration in foreach loops within tests
        class Collection implements \IteratorAggregate {
            public function addAttributeToSelect($attribute) {}
            public function getIterator(): \Traversable { return new \ArrayIterator([]); }
        }
    }
}

namespace Magento\Framework\View\Element\Block {
    if (!interface_exists('Magento\Framework\View\Element\Block\ArgumentInterface')) {
        interface ArgumentInterface {}
    }
}
