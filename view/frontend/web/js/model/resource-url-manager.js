define(
    [
        'jquery',
        'Magento_Checkout/js/model/resource-url-manager'
    ],
    function ($, resourceUrlManager) {
        "use strict";

        return $.extend({

            getUrlForUpdateItem: function (quoteId) {
                var params = (this.getCheckoutMethod() == 'guest') ? {cartId: quoteId} : {};
                var urls = {
                    'guest': '/light_checkout/guest-carts/:cartId/quote-items',
                    'customer': '/light_checkout/carts/mine/quote-items'
                };
                return this.getUrl(urls, params);
            },

            getUrlForRemoveItem: function (quoteId, itemId) {
                var params = (this.getCheckoutMethod() == 'guest')
                    ? {
                        cartId: quoteId,
                        itemId: itemId
                    }
                    : {
                        itemId: itemId
                    };

                var urls = {
                    'guest': '/light_checkout/guest-carts/:cartId/quote-items/:itemId',
                    'customer': '/light_checkout/carts/mine/quote-items/:itemId'
                };
                return this.getUrl(urls, params);
            }
        }, resourceUrlManager);
    }
);