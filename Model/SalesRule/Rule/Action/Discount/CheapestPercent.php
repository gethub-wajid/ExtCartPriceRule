<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

use Magento\Framework\App\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

/**
 * Class CheapestPercent
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
class CheapestPercent extends AbstractRule
{
    /**
     * CheapestPercent constructor.
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
    private function _calculate($rule , $item)
    {
        $discountData = $this->discountFactory->create();
        $address = $item->getAddress();
        $items = $address->getAllItems();
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(self::CUSTOM_RULE_FIELDS_TABLE)->where('salesrule_id = '.$rule->getId());
        $ruleCustomAttributes = $connection->fetchAll($select)[0];
        $percDisAmount = $ruleCustomAttributes['percent_discount_amount'];
        $maxQty = (int)$rule->getDiscountQty();
        $discountedItem = $this->getCheapestItem($items);
        if ($discountedItem->getProduct()->getId() == $item->getProduct()->getId()) {
            $discountQty = $discountedItem->getQty();
            if ($discountQty > $maxQty) {
                $discountQty = $maxQty;
            }
            $discountAmount = ($discountedItem->getPrice()) * ($percDisAmount/100) * $discountQty;
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }

    /**
     * @param $items
     * @return mixed
     */
    private function getCheapestItem($items)
    {
        $cheapestItemKey = '';
        foreach ($items as $key => $item) {
            if ($cheapestItemKey === '') {
                $cheapestItemKey = $key;
            } elseif ($item->getProduct()->getPrice() < $items[$cheapestItemKey]->getProduct()->getPrice()) {
                $cheapestItemKey = $key;
            }
        }
        return $items[$cheapestItemKey];
    }
}
