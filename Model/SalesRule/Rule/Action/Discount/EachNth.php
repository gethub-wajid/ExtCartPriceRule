<?php

namespace PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount;

/**
 * Class EachNth
 * @package PHPStudios\ExtCartPriceRules\Model\SalesRule\Rule\Action\Discount
 */
abstract class EachNth extends AbstractRule
{
    /**
     * @param $items
     * @return array
     */
    protected function getFilteredItems($items)
    {
        $filteredProducts = [];
        foreach ($items as $item) {
            if (!$item->getParentItem()) {
                $filteredProducts[] = $item;
            }
        }
        return $filteredProducts;
    }

    /**
     * @param $items
     * @param $curItem
     * @param $itemsCount
     * @param $eachNth
     * @param null $afterMth
     * @return array|false[]
     */
    protected function isCurrentItemNth($items , $curItem , $itemsCount , $eachNth , $afterMth = null)
    {
        $isItemNthData = ['isItemNth' => false];
        if ($afterMth == null) {
            $discountedKey = $eachNth;
        } elseif ($afterMth > 0 && $afterMth <= $itemsCount) {
            $discountedKey = $afterMth + $eachNth;
        } else {
            $discountedKey = $itemsCount + 1;
        }
        while ($discountedKey <= $itemsCount) {
            $discountedKeyIndex = $discountedKey - 1;
            if ($items[$discountedKeyIndex]->getProduct()->getId() == $curItem->getProduct()->getId()) {
                return ['isItemNth' => true , 'itemIndex' => $discountedKeyIndex];
            }
            $discountedKey += $eachNth;
        }
        return $isItemNthData;
    }
}
