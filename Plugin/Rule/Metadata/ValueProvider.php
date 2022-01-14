<?php

namespace PHPStudios\ExtCartPriceRules\Plugin\Rule\Metadata;

use Magento\Framework\App\Config;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ValueProvider
 * @package PHPStudios\ExtCartPriceRules\Plugin\Rule\Metadata
 */
class ValueProvider
{
    const ENABLED_EXT_CONFIG = 'ext_cart_price_rules/ext_cart_group/enabled';

    /**
     * @var Config
     */
    protected $scopeConfig;

    /**
     * ValueProvider constructor.
     * @param Config $scopeConfig
     */
    public function __construct(Config $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param SalesRuleValueProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetMetadataValues(SalesRuleValueProvider $subject, $result)
    {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        if (!$this->isEnabledExtCartPriceRules()) {
            return $result;
        }
        $generalActions = [
            [
                'label' => __('Percent of Most Expensive Product Price Discount'),
                'value' => 'expensive_percent'
            ],
            [
                'label' => __('Percent of Cheapest Product Price Discount'),
                'value' => 'cheapest_percent'
            ],
            [
                'label' => __('Get $Y for each $X spent'),
                'value' => 'money_amount'
            ]
        ];

        $actions[] = [
            'label' => __('Common discount types'),
            'value' => $generalActions
        ];

        $buyXgetYActions = [
            [
                'label' => __('Buy X get percent of Y Free'),
                'value' => 'buyx_gety_percent'
            ],
            [
                'label' => __('Buy X get Y with fixed discount amount'),
                'value' => 'buyx_gety_fixeddiscount'
            ],
            [
                'label' => __('Buy X get Y for fixed price'),
                'value' => 'buyx_gety_fixedprice'
            ]
        ];

        $actions[] = [
            'label' => __('Buy X get Y (X and Y are different products)'),
            'value' => $buyXgetYActions
        ];

        $eachNthActions = [
            [
                'label' => __('Each 2-d, 4-th, 6-th with X% 0ff'),
                'value' => 'each_nth_percentdiscount'
            ],
            [
                'label' => __('Each 3-d, 6-th, 9-th with $X 0ff'),
                'value' => 'each_nth_fixeddiscount'
            ],
            [
                'label' => __('Each 5th, 10th, 15th for $X'),
                'value' => 'each_nth_fixedprice'
            ]
        ];

        $actions[] = [
            'label' => __('Each N-th'),
            'value' => $eachNthActions
        ];

        $eachNthAfterMActions = [
            [
                'label' => __('Each 2-d, 4-th, 6-th after Mth product with X% 0ff'),
                'value' => 'each_nth_after_mth_percent'
            ],
            [
                'label' => __('Each 3-d, 6-th, 9-th after Mth product with $X 0ff'),
                'value' => 'each_nth_after_mth_fixeddiscount'
            ],
            [
                'label' => __('Each 5th, 10th, 15th after Mth product for $X'),
                'value' => 'each_nth_after_mth_fixedprice'
            ]
        ];

        $actions[] = [
            'label' => __('Each N-th after M'),
            'value' => $eachNthAfterMActions
        ];

        return $result;
    }

    /**
     * @return array|mixed
     */
    public function isEnabledExtCartPriceRules()
    {
        return $this->scopeConfig->getValue(
            self::ENABLED_EXT_CONFIG,
            ScopeInterface::SCOPE_STORE
        );
    }
}
