<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

/**
 * Class BuyxGety
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
abstract class BuyxGety extends AbstractRule
{
    /**
     * @param $item
     * @param $ruleCustomAttributes
     * @return array
     */
    protected function getXYproducts($item , $ruleCustomAttributes)
    {
        $address = $item->getAddress();
        $items = $address->getAllItems();
        $products = [];
        foreach ($items as $curItem) {
            $curItemType = $curItem->getProduct()->getData('type_id');
            $promoSkus = (trim($ruleCustomAttributes['promo_skus']) != "") ? explode(',' , $ruleCustomAttributes['promo_skus']) : [];
            if (in_array($curItem->getSku() , $promoSkus)) {
                $products['y'][] = $curItem;
                continue;
            }
            $curItemCats = $curItem->getCategoryIds();
            if (!$curItemCats) {
                $curItemCats = $curItem->getProduct()->getCategoryIds();
            }
            $promoCats = (trim($ruleCustomAttributes['promo_categories']) != "") ? explode(',' , $ruleCustomAttributes['promo_categories']) : [];
            if (array_intersect($promoCats , $curItemCats) && ($curItemType == 'simple')) {
                $products['y'][] = $curItem;
            } else if($curItemType == 'simple') {
                $products['x'][] = $curItem;
            }
        }
        return $products;
    }

    /**
     * @param $productsItems
     * @return int
     */
    protected function getQty($productsItems)
    {
        $qty = 0;
        foreach ($productsItems as $item) {
            if ($item->getProduct()->getData('type_id') == 'simple') {
                $qty += $item->getQty();
            }
        }
        return $qty;
    }
}
