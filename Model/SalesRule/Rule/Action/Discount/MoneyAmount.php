<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

use Magento\Framework\App\Config;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\SalesRule\Model\Validator;

/**
 * Class MoneyAmount
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
class MoneyAmount extends AbstractRule
{
    /**
     * MoneyAmount constructor.
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $resourceConnection
     * @param Config $scopeConfig
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        ResourceConnection $resourceConnection,
        Config $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        if (!$this->isEnabledExtCartPriceRules()) {
            return $this->discountFactory->create();
        }
        $discountData = $this->_calculate($rule, $item);
        return $discountData;
    }

    /**
     * @param $rule
     * @param $item
     * @return Data
     */
    protected function _calculate($rule, $item)
    {
        $discountData = $this->discountFactory->create();
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(self::CUSTOM_RULE_FIELDS_TABLE)->where('salesrule_id = '.$rule->getId());
        $ruleCustomAttributes = $connection->fetchAll($select)[0];
        $discountAmount = $rule->getDiscountAmount();
        $amountSpent = $ruleCustomAttributes['amount_spent'];
        $maxDisAmount = $ruleCustomAttributes['maximum_total_discount'];
        $address = $item->getAddress();
        $items = $address->getAllItems();
        if ($items[0]->getProduct()->getId() != $item->getProduct()->getId()) {
            return $discountData;
        }
        $subTotal = $address->getSubtotal();
        $discount = (int)($subTotal/$amountSpent) * $discountAmount;
        if ($discount > $maxDisAmount) {
            $discount = $maxDisAmount;
        }
        $discountData->setAmount($discount);
        $discountData->setBaseAmount($discount);
        $discountData->setOriginalAmount($discount);
        $discountData->setBaseOriginalAmount($discount);
        return $discountData;
    }
}

