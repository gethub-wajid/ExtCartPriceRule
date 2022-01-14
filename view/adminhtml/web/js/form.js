define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {

    'use strict';

    return select.extend({
        simpleAction : uiRegistry.get('index = simple_action'),
        discountAmount : uiRegistry.get('index = discount_amount'),
        percentDiscountAmount : uiRegistry.get('index = percent_discount_amount'),
        maxDiscountAmount : uiRegistry.get('index = maximum_discount_amount'),
        maxTotalDiscount : uiRegistry.get('index = maximum_total_discount'),
        numXProducts : uiRegistry.get('index = num_x_products'),
        numYProducts : uiRegistry.get('index = num_y_products'),
        maxQtyDiscount : uiRegistry.get('index = discount_qty'),
        amountSpentX : uiRegistry.get('index = amount_spent'),
        eachNth : uiRegistry.get('index = each_nth'),
        afterMth : uiRegistry.get('index = after_mth'),
        discountStep : uiRegistry.get('index = discount_step'),
        promoSkus : uiRegistry.get('index = promo_skus'),
        promoCategories : uiRegistry.get('index = promo_categories'),
        dependentFieldNames: [
            'buyx_gety_percent',
            'buyx_gety_fixeddiscount',
            'buyx_gety_fixedprice'
        ],

        initialize: function () {
            var fieldValue = this._super().initialValue;
            this.onUpdate(fieldValue);
            return this;
        },

        /**
         *
         * @param value
         * @returns {*}
         */
        onUpdate: function (value) {
            this.setFieldsToDefault();
            switch (value) {
                case 'expensive_percent':
                case 'cheapest_percent':
                    this.discountAmount.hide();
                    this.discountStep.hide();
                    this.percentDiscountAmount.show();
                    // this.maxQtyDiscount.hide();
                    break;
                case 'money_amount':
                    this.discountStep.hide();
                    this.maxTotalDiscount.show();
                    this.amountSpentX.show();
                    this.maxQtyDiscount.hide();
                    break;
                case 'buyx_gety_percent':
                    this.discountAmount.hide();
                    this.percentDiscountAmount.show();
                    this.buyXgetYCase();
                    break;
                case 'buyx_gety_fixeddiscount':
                    this.buyXgetYCase();
                    break;
                case 'buyx_gety_fixedprice':
                    this.buyXgetYCase();
                    break;
                case 'each_nth_percentdiscount':
                    this.discountAmount.hide();
                    this.percentDiscountAmount.show();
                    this.eachNthCase();
                    break;
                case 'each_nth_fixeddiscount':
                    this.eachNthCase();
                    break;
                case 'each_nth_fixedprice':
                    this.eachNthCase();
                    break;
                case 'each_nth_after_mth_percent':
                    this.discountAmount.hide();
                    this.percentDiscountAmount.show();
                    this.eachNthAfterMthCase();
                    break;
                case 'each_nth_after_mth_fixeddiscount':
                    this.eachNthAfterMthCase();
                    break;
                case 'each_nth_after_mth_fixedprice':
                    this.eachNthAfterMthCase();
                    break;
                default:
                    break;
            }
            return this._super();
        },
        setFieldsToDefault: function () {
            this.promoSkus.hide();
            this.promoCategories.hide();
            this.discountAmount.show();
            this.percentDiscountAmount.hide();
            this.maxDiscountAmount.hide();
            this.maxTotalDiscount.hide();
            this.numXProducts.hide();
            this.numYProducts.hide();
            this.maxQtyDiscount.show();
            this.discountStep.show();
            this.amountSpentX.hide();
            this.eachNth.hide();
            this.afterMth.hide();
        },
        buyXgetYCase: function () {
            this.maxDiscountAmount.show();
            this.numXProducts.show();
            this.numYProducts.show();
            this.promoSkus.show();
            this.promoCategories.show();
            this.discountStep.hide();
        },
        eachNthCase: function () {
            this.discountStep.hide();
            this.maxDiscountAmount.show();
            this.eachNth.show();
        },
        eachNthAfterMthCase: function () {
            this.discountStep.hide();
            this.maxDiscountAmount.show();
            this.eachNth.show();
            this.afterMth.show();
        }
    });
});
