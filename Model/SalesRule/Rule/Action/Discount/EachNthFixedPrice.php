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
 * Class EachNthFixedPrice
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
class EachNthFixedPrice extends EachNth
{
    /**
     * EachNthFixedPrice constructor.
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
        $fixedDisPrice = $rule->getDiscountAmount();
        $maxDisAmount = $ruleCustomAttributes['maximum_discount_amount'];
        $maxQtyDiscounted = $rule->getDiscountQty();
        if ($maxQtyDiscounted > 0) {
            $eachNth = $ruleCustomAttributes['each_nth'];
            $address = $item->getAddress();
            $items = $address->getAllItems();
            $filteredItems = $this->getFilteredItems($items);
            $isItemNthData = $this->isCurrentItemNth($filteredItems , $item , count($filteredItems) , $eachNth);
            if ($isItemNthData['isItemNth'] == true) {
                $itemKey = $isItemNthData['itemIndex'];
                $itemPrice = $filteredItems[$itemKey]->getPrice();
                $itemQty = $filteredItems[$itemKey]->getQty();
                if ($itemQty > $maxQtyDiscounted) {
                    $itemQty = $maxQtyDiscounted;
                }
                $discountOnItem = ($itemPrice >= $fixedDisPrice) ? ($itemPrice - $fixedDisPrice) : 0;
                $totalDiscountAmount = $discountOnItem * $itemQty;
                if ($totalDiscountAmount > $maxDisAmount) {
                    $totalDiscountAmount = $maxDisAmount;
                }
                $discountData->setAmount($totalDiscountAmount);
                $discountData->setBaseAmount($totalDiscountAmount);
                $discountData->setOriginalAmount($totalDiscountAmount);
                $discountData->setBaseOriginalAmount($totalDiscountAmount);
            }
        }
        return $discountData;
    }
}

