define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress();            
            if (billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }

                var attribute = billingAddress.customAttributes.find(
                    function (element) {
                        return element.attribute_code === 'area';
                    }
                );

                billingAddress['extension_attributes']['area'] = attribute.value;
            }

            return originalAction(messageContainer);
        });
    };
});