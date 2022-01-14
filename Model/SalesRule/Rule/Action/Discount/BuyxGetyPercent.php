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
 * Class BuyxGetyPercent
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
class BuyxGetyPercent extends BuyxGety
{
    /**
     * BuyxGetyPercent constructor.
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
        $xyProducts = $this->getXYproducts($item , $ruleCustomAttributes);
        if (isset($xyProducts['x']) && isset($xyProducts['y'])) {
            $xQty = $this->getQty($xyProducts['x']);
            $yQty = $this->getQty($xyProducts['y']);
            $numXProducts = $ruleCustomAttributes['num_x_products'];
            $numYProducts = $ruleCustomAttributes['num_y_products'];
            $percDisAmount = $ruleCustomAttributes['percent_discount_amount'];
            $maxDisAmount = $ruleCustomAttributes['maximum_discount_amount'];
            $maxQty = (int)$rule->getDiscountQty();
            $discountAmount = 0;
            if ($xQty >= $numXProducts && $maxQty > 0) {
                $yDiscQty = (int)($xQty/$numXProducts)*$numYProducts;
                if ($yDiscQty > $yQty) {
                    $yDiscQty = $yQty;
                }
                $yQtyUsed = $this->getYProductQtyConsumed($xyProducts['y'] , $item , $percDisAmount , $maxDisAmount , $maxQty , $yDiscQty);
                if ($yQtyUsed >= $yDiscQty) {
                    return $discountData;
                }
                $yQtyRemaining = $yDiscQty - $yQtyUsed;
                foreach ($xyProducts['y'] as $yProductItem) {
                    if (($item->getProduct()->getData('type_id') == 'simple')
                        && ($yProductItem->getProduct()->getId() == $item->getProduct()->getId())) {
                        for ($i=0; $i<$yProductItem->getQty(); $i++) {
                            $discountAmount += ($yProductItem->getPrice())*($percDisAmount/100);
                            if ($discountAmount > $maxDisAmount) {
                                $discountAmount = $maxDisAmount;
                                break;
                            }
                            if (--$maxQty <= 0 || --$yQtyRemaining <= 0) {
                                break;
                            }
                        }
                    }
                    if ($discountAmount == $maxDisAmount || $maxQty <= 0 || $yQtyRemaining <= 0) {
                        break;
                    }
                }
            }
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }

    /**
     * @param $yProducts
     * @param $item
     * @param $percDisAmount
     * @param $maxDisAmountPerItem
     * @param $maxQtyPerItem
     * @param $yDiscQty
     * @return int
     */
    private function getYProductQtyConsumed($yProducts , $item , $percDisAmount , $maxDisAmountPerItem , $maxQtyPerItem , $yDiscQty) {
        $yQtyUsed = 0;
        $discountAmount = 0;
        foreach ($yProducts as $yProductItem) {
            if ($yProductItem->getProduct()->getId() == $item->getProduct()->getId()) {
                return $yQtyUsed;
            }
            if ($item->getProduct()->getData('type_id') == 'simple') {
                $maxQtyPerItemTemp = $maxQtyPerItem;
                $discOnItem = 0;
                for ($i=0; $i<$yProductItem->getQty(); $i++) {
                    $discountAmount += ($yProductItem->getPrice())*($percDisAmount/100);;
                    $discOnItem += ($yProductItem->getPrice())*($percDisAmount/100);;
                    if ($discOnItem > $maxDisAmountPerItem) {
                        $discountAmount = ($discountAmount - $discOnItem) + $maxDisAmountPerItem;
                        $yQtyUsed++;
                        break;
                    }
                    if (($maxQtyPerItemTemp -1) > 0) {
                        $yQtyUsed++;
                    }
                    if (--$maxQtyPerItemTemp <= 0) {
                        break;
                    }
                    if (--$yDiscQty <= 0) {
                        return $yQtyUsed;
                    }
                }
            }
            if ($yDiscQty <= 0) {
                return $yQtyUsed;
            }
        }
        return $yQtyUsed;
    }
}
