<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

use Magento\Framework\ObjectManagerInterface;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory as SalesRuleCalculatorFactory;

/**
 * Class CalculatorFactory
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
class CalculatorFactory extends SalesRuleCalculatorFactory
{
    const EXPENSIVE_PERCENT_ACTION = 'expensive_percent';
    const CHEAPEST_PERCENT_ACTION = 'cheapest_percent';
    const MONEY_AMOUNT = 'money_amount';
    const BUYX_GETY_PERCENT = 'buyx_gety_percent';
    const BUYX_GETY_FIXEDDISCOUNT = 'buyx_gety_fixeddiscount';
    const BUYX_GETY_FIXEDPRICE = 'buyx_gety_fixedprice';
    const EACH_NTH_PERCENT_DISCOUNT = 'each_nth_percentdiscount';
    const EACH_NTH_FIXED_DISCOUNT = 'each_nth_fixeddiscount';
    const EACH_NTH_FIXED_PRICE = 'each_nth_fixedprice';
    const EACH_NTH_AFTER_MTH_PERCENT_DISCOUNT = 'each_nth_after_mth_percent';
    const EACH_NTH_AFTER_MTH_FIXED_DISCOUNT = 'each_nth_after_mth_fixeddiscount';
    const EACH_NTH_AFTER_MTH_FIXED_PRICE = 'each_nth_after_mth_fixedprice';

    private $discountRules = [
        self::EXPENSIVE_PERCENT_ACTION =>
            ExpensivePercent::class,
        self::CHEAPEST_PERCENT_ACTION =>
            CheapestPercent::class,
        self::MONEY_AMOUNT =>
            MoneyAmount::class,
        self::BUYX_GETY_PERCENT =>
            BuyxGetyPercent::class,
        self::BUYX_GETY_FIXEDDISCOUNT =>
            BuyxGetyFixedDiscount::class,
        self::BUYX_GETY_FIXEDPRICE =>
            BuyxGetyFixedPrice::class,
        self::EACH_NTH_PERCENT_DISCOUNT =>
            EachNthPercent::class,
        self::EACH_NTH_FIXED_DISCOUNT =>
            EachNthFixed::class,
        self::EACH_NTH_FIXED_PRICE =>
            EachNthFixedPrice::class,
        self::EACH_NTH_AFTER_MTH_PERCENT_DISCOUNT =>
            EachNthAfterMthPercent::class,
        self::EACH_NTH_AFTER_MTH_FIXED_DISCOUNT =>
            EachNthAfterMthFixed::class,
        self::EACH_NTH_AFTER_MTH_FIXED_PRICE =>
            EachNthAfterMthFixedPrice::class,
    ];

    /**
     * CalculatorFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param array $discountRules
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $discountRules = []
    ) {
        $discountRules = array_merge($discountRules, $this->discountRules);
        parent::__construct($objectManager, $discountRules);
    }
}
