define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, priceUtils, totals) {
    'use strict';
    var show_hide_igst_blockConfig = window.checkoutConfig.show_hide_igst_block;
    var igst_label = window.checkoutConfig.igst_label;
    var custom_igst_amount = window.checkoutConfig.igst_charge;

    return Component.extend({

        totals: quote.getTotals(),
        canVisibleIgstBlock: show_hide_igst_blockConfig,
        getIgstFormattedPrice: ko.observable(priceUtils.formatPrice(custom_igst_amount, quote.getPriceFormat())),
        getIgstLabel:ko.observable(igst_label),
        isDisplayed: function () {
			
            return this.getIgstValue() != 0;
        },
        getIgstValue: function() {
            var price = 0;

            if (this.totals()) {
                price = totals.getSegment('igst_charge').value;
            }
           return this.getIgstFormattedPrice(priceUtils.formatPrice(price));
        }
    });
});
