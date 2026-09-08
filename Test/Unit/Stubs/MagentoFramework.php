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

namespace Magento\Framework\App\Config {
    if (!interface_exists('Magento\Framework\App\Config\ScopeConfigInterface')) {
        interface ScopeConfigInterface {
            public function getValue($path, $scopeType = 'default', $scopeCode = null);
            public function isSetFlag($path, $scopeType = 'default', $scopeCode = null);
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
            public function getAllVisibleItems() {}
        }
    }
}

namespace Magento\Sales\Model\Order {
    if (!class_exists('Magento\Sales\Model\Order\Item')) {
        class Item {
            public function getPrice() {}
            public function getDiscountAmount() {}
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
            public function getId() {}
            public function getCategoryIds() {}
            public function getCategoryCollection() {}
            public function getResource() {}
            public function getAttributeText($attributeCode) {}
            public function getData($key = '', $index = null) {}
        }
    }

    if (!class_exists('Magento\Catalog\Model\Category')) {
        class Category {
            public function getId() {}
            public function getName() {}
            public function getPath() {}
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
            public function addIdFilter($ids) {}
            public function getIterator(): \Traversable { return new \ArrayIterator([]); }
        }
    }

    if (!class_exists('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')) {
        class CollectionFactory {
            public function create() {}
        }
    }
}

namespace Magento\Store\Model {
    if (!interface_exists('Magento\Store\Model\ScopeInterface')) {
        interface ScopeInterface {
            const SCOPE_STORE = 'store';
            const SCOPE_STORES = 'stores';
            const SCOPE_WEBSITE = 'website';
            const SCOPE_WEBSITES = 'websites';
            const SCOPE_GROUP = 'group';
        }
    }
}

namespace Magento\Framework\View\Element\Block {
    if (!interface_exists('Magento\Framework\View\Element\Block\ArgumentInterface')) {
        interface ArgumentInterface {}
    }
}

namespace Magento\Framework\Event {
    if (!interface_exists('Magento\Framework\Event\ObserverInterface')) {
        interface ObserverInterface {
            public function execute(Observer $observer);
        }
    }

    if (!class_exists('Magento\Framework\Event\Observer')) {
        class Observer {}
    }
}

namespace Magento\Framework\View\Page {
    if (!class_exists('Magento\Framework\View\Page\Config')) {
        class Config {
            public function addRemotePageAsset($url, $contentType, array $properties = []) {}
        }
    }
}

namespace Psr\Log {
    if (!interface_exists('Psr\Log\LoggerInterface')) {
        interface LoggerInterface {
            public function emergency($message, array $context = []);
            public function alert($message, array $context = []);
            public function critical($message, array $context = []);
            public function error($message, array $context = []);
            public function warning($message, array $context = []);
            public function notice($message, array $context = []);
            public function info($message, array $context = []);
            public function debug($message, array $context = []);
            public function log($level, $message, array $context = []);
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel\Product {
    if (!class_exists('Magento\Catalog\Model\ResourceModel\Product\Collection')) {
        class Collection implements \IteratorAggregate {
            public function addAttributeToSelect($attribute) {}
            public function addIdFilter($ids) {}
            public function getIterator(): \Traversable { return new \ArrayIterator([]); }
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel\Product {
    if (!class_exists('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')) {
        class CollectionFactory {
            public function create() {}
        }
    }
}

namespace Magento\Framework\Model{
    if (!class_exists('Magento\Framework\Model\Context')) {
        class Context {
            public function __construct() {}
        }
    }
}

namespace Magento\Framework{
    if(!class_exists('Magento\Framework\Registry')) {
        class Registry {
            public function __construct() {}
        }
    }
}

namespace Magento\Framework\App\Cache {
    if (!interface_exists('Magento\Framework\App\Cache\TypeListInterface')) {
        interface TypeListInterface {
            public function clean($type);
        }
    }
}

namespace Magento\Framework\Serialize {
    if (!interface_exists('Magento\Framework\Serialize\SerializerInterface')) {
        interface SerializerInterface {
            public function serialize($data);
            public function unserialize($data);
        }
    }
}

namespace Magento\Config\Model\Config\Backend\Serialized {
    if (!class_exists('Magento\Config\Model\Config\Backend\Serialized\ArraySerialized')) {
        class ArraySerialized {
            protected $value;

            public function setValue($value) {
                $this->value = $value;
                return $this;
            }

            public function getValue() {
                return $this->value;
            }

            public function beforeSave() {
                return $this;
            }
        }
    }
}

namespace Magento\Framework\Exception {
    if (!class_exists('Magento\Framework\Exception\ValidatorException')) {
        class ValidatorException extends \Exception {}
    }
}

namespace TwoPerformant\BusinessLeagueMarketing\Model\Config\Backend {
    if (!function_exists(__NAMESPACE__ . '\\__')) {
        function __($text) {
            return $text;
        }
    }
}
