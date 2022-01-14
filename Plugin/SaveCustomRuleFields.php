<?php

namespace PHPStudios\ExtCartPriceRules\Plugin;

use Closure;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Save;
use Magento\Framework\App\ResourceConnection;

/**
 * Class SaveCustomRuleFields
 * @package PHPStudios\ExtCartPriceRules\Plugin
 */
class SaveCustomRuleFields
{

    const SALESRULE_TABLE = 'salesrule';
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
     * SaveCustomRuleFields constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Save $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundExecute(Save $subject, Closure $proceed)
    {
        $result = $proceed();
        $simpleAction = $_POST['simple_action'];
        if (in_array($simpleAction , self::CUSTOM_ACTIONS)) {
            $connection = $this->resourceConnection->getConnection();
            if (isset($_POST['rule_id'])) {
                $salesRuleId = $_POST['rule_id'];
            } else {
                $selectStmt = $connection->select()->from(self::SALESRULE_TABLE , 'Max(rule_id)');
                $row = $connection->fetchAll($selectStmt);
                $salesRuleId = $row[0]['Max(rule_id)'];
            }
            $columnValues = [];
            switch ($simpleAction){
                case 'expensive_percent':
                case 'cheapest_percent':
                    $percentDiscountAmount = $_POST['percent_discount_amount'];
                    $columnValues = [
                        'salesrule_id' => $salesRuleId,
                        'percent_discount_amount' => $percentDiscountAmount
                    ];
                    break;
                case 'money_amount':
                    $maxTotalDiscount = $_POST['maximum_total_discount'];
                    $amountSpentX = $_POST['amount_spent'];
                    $columnValues = [
                        'salesrule_id' => $salesRuleId,
                        'maximum_total_discount' => $maxTotalDiscount,
                        'amount_spent' => $amountSpentX
                    ];
                    break;
                case 'buyx_gety_percent':
                    $percentDiscountAmount = $_POST['percent_discount_amount'];
                    $columnValues = $this->buyXgetYCommonData($salesRuleId);
                    $columnValues['percent_discount_amount'] = $percentDiscountAmount;
                    break;
                case 'buyx_gety_fixeddiscount':
                case 'buyx_gety_fixedprice':
                    $columnValues = $this->buyXgetYCommonData($salesRuleId);
                    break;
                case 'each_nth_percentdiscount':
                    $percentDiscountAmount = $_POST['percent_discount_amount'];
                    $columnValues = $this->getEachNthCommonData($salesRuleId);
                    $columnValues['percent_discount_amount'] = $percentDiscountAmount;
                    break;
                case 'each_nth_fixeddiscount':
                case 'each_nth_fixedprice':
                    $columnValues = $this->getEachNthCommonData($salesRuleId);
                    break;
                case 'each_nth_after_mth_percent':
                    $percentDiscountAmount = $_POST['percent_discount_amount'];
                    $columnValues = $this->getEachNthAfterMthCommonData($salesRuleId);
                    $columnValues['percent_discount_amount'] = $percentDiscountAmount;
                    break;
                case 'each_nth_after_mth_fixeddiscount':
                case 'each_nth_after_mth_fixedprice':
                    $columnValues = $this->getEachNthAfterMthCommonData($salesRuleId);
                    break;
                default:
                    break;
            }
            $select = $connection->select()->from(self::CUSTOM_RULE_FIELDS_TABLE)->where('salesrule_id = '.$columnValues['salesrule_id']);
            $col = $connection->fetchCol($select);
            if ($col) {
                $where = 'salesrule_id = '.$salesRuleId;
                $connection->update(self::CUSTOM_RULE_FIELDS_TABLE , $columnValues , $where);
            } else {
                $connection->insert(self::CUSTOM_RULE_FIELDS_TABLE , $columnValues);
            }
        }
        return $result;
    }

    /**
     * @param $salesRuleId
     * @return array
     */
    private function getEachNthCommonData($salesRuleId) {
        $maxDiscountAmount = $_POST['maximum_discount_amount'];
        $eachNth = $_POST['each_nth'];
        $columnValues = [
            'salesrule_id' => $salesRuleId,
            'maximum_discount_amount' => $maxDiscountAmount,
            'each_nth' => $eachNth,
        ];
        return $columnValues;
    }

    /**
     * @param $salesRuleId
     * @return array
     */
    private function getEachNthAfterMthCommonData($salesRuleId) {
        $maxDiscountAmount = $_POST['maximum_discount_amount'];
        $afterMth = $_POST['after_mth'];
        $eachNth = $_POST['each_nth'];
        $columnValues = [
            'salesrule_id' => $salesRuleId,
            'maximum_discount_amount' => $maxDiscountAmount,
            'each_nth' => $eachNth,
            'after_mth' => $afterMth
        ];
        return $columnValues;
    }

    /**
     * @param $salesRuleId
     * @return array
     */
    private function buyXgetYCommonData($salesRuleId) {
        $maxDiscountAmount = $_POST['maximum_discount_amount'];
        $numXProducts = $_POST['num_x_products'];
        $numYProducts = $_POST['num_y_products'];
        $yPromoSkus = $_POST['promo_skus'];
        $yPromoCats = $_POST['promo_categories'];
        $columnValues = [
            'salesrule_id' => $salesRuleId,
            'maximum_discount_amount' => $maxDiscountAmount,
            'num_x_products' => $numXProducts,
            'num_y_products' => $numYProducts,
            'promo_skus' => $yPromoSkus,
            'promo_categories' => $yPromoCats
        ];
        return $columnValues;
    }
}
