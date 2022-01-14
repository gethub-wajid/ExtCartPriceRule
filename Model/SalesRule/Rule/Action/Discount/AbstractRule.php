<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AbstractRule
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
abstract class AbstractRule extends AbstractDiscount
{
    const ENABLED_EXT_CONFIG   = 'ext_cart_price_rules/ext_cart_group/enabled';
    const CUSTOM_RULE_FIELDS_TABLE = 'phpstudios_promotions_salesrule';

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Config
     */
    protected $scopeConfig;

    /**
     * @return array|mixed
     */
    protected function isEnabledExtCartPriceRules()
    {
        return $this->scopeConfig->getValue(
            self::ENABLED_EXT_CONFIG,
            ScopeInterface::SCOPE_STORE
        );
    }

}
