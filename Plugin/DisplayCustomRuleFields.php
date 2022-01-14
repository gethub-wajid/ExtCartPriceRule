<?php

namespace PHPStudios\ExtCartPriceRules\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\SalesRule\Model\Rule\DataProvider;

/**
 * Class DisplayCustomRuleFields
 * @package PHPStudios\ExtCartPriceRules\Plugin
 */
class DisplayCustomRuleFields
{
    const CUSTOM_RULE_FIELDS_TABLE = 'phpstudios_promotions_salesrule';
    const CUSTOM_ACTIONS = [
        'expensive_percent','cheapest_percent','money_amount','buyx_gety_percent',
        'buyx_gety_percent','buyx_gety_fixeddiscount','buyx_gety_fixedprice',
        'each_nth_percentdiscount','each_nth_fixeddiscount','each_nth_fixedprice',
        'each_nth_after_mth_percent','each_nth_after_mth_fixeddiscount','each_nth_after_mth_fixedprice'
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * DisplayCustomRuleFields constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param DataProvider $subject
     * @param $result
     * @return array|null
     */
    public function afterGetData(DataProvider $subject , $result)
    {
        if ($result) {
            foreach ($result as $ruleId => $rule) {
                $simpleAction = $rule['simple_action'];
                if (in_array($simpleAction , self::CUSTOM_ACTIONS)) {
                    $connection = $this->resourceConnection->getConnection();
                    $select = $connection->select()->from(self::CUSTOM_RULE_FIELDS_TABLE)->where('salesrule_id = '.$ruleId);
                    $row = $connection->fetchAll($select);
                    if ($row) {
                        switch ($simpleAction){
                            case 'expensive_percent':
                            case 'cheapest_percent':
                                $result[$ruleId]['percent_discount_amount'] = $row[0]['percent_discount_amount'];
                                break;
                            case 'money_amount':
                                $result[$ruleId]['maximum_total_discount'] = $row[0]['maximum_total_discount'];
                                $result[$ruleId]['amount_spent'] = $row[0]['amount_spent'];
                                break;
                            case 'buyx_gety_percent':
                                $result[$ruleId]['percent_discount_amount'] = $row[0]['percent_discount_amount'];
                                $this->buyXgetYCommonData($result , $row , $ruleId);
                                break;
                            case 'buyx_gety_fixeddiscount':
                            case 'buyx_gety_fixedprice':
                                $this->buyXgetYCommonData($result , $row , $ruleId);
                                break;
                            case 'each_nth_percentdiscount':
                                $result[$ruleId]['percent_discount_amount'] = $row[0]['percent_discount_amount'];
                                $this->getEachNthCommonData($result , $row , $ruleId);
                                break;
                            case 'each_nth_fixeddiscount':
                            case 'each_nth_fixedprice':
                                $this->getEachNthCommonData($result , $row , $ruleId);
                                break;
                            case 'each_nth_after_mth_percent':
                                $result[$ruleId]['percent_discount_amount'] = $row[0]['percent_discount_amount'];
                                $this->getEachNthAfterMthCommonData($result , $row , $ruleId);
                                break;
                            case 'each_nth_after_mth_fixeddiscount':
                            case 'each_nth_after_mth_fixedprice':
                                $this->getEachNthAfterMthCommonData($result , $row , $ruleId);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $result
     * @param $row
     * @param $salesRuleId
     */
    private function getEachNthCommonData(& $result , $row , $salesRuleId) {
        $result[$salesRuleId]['maximum_discount_amount'] = $row[0]['maximum_discount_amount'];
        $result[$salesRuleId]['each_nth'] = $row[0]['each_nth'];
    }

    /**
     * @param $result
     * @param $row
     * @param $salesRuleId
     */
    private function getEachNthAfterMthCommonData(& $result , $row , $salesRuleId) {
        $result[$salesRuleId]['maximum_discount_amount'] = $row[0]['maximum_discount_amount'];
        $result[$salesRuleId]['after_mth'] = $row[0]['after_mth'];
        $result[$salesRuleId]['each_nth'] = $row[0]['each_nth'];
    }

    /**
     * @param $result
     * @param $row
     * @param $salesRuleId
     */
    private function buyXgetYCommonData(& $result , $row , $salesRuleId) {
        $result[$salesRuleId]['maximum_discount_amount'] = $row[0]['maximum_discount_amount'];
        $result[$salesRuleId]['num_x_products'] = $row[0]['num_x_products'];
        $result[$salesRuleId]['num_y_products'] = $row[0]['num_y_products'];
        $result[$salesRuleId]['promo_skus'] = $row[0]['promo_skus'];
        $result[$salesRuleId]['promo_categories'] = $row[0]['promo_categories'];
    }
}
