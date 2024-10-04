define([
    'Magento_Checkout/js/model/shipping-address'
], function (shippingAddress) {
    'use strict';

    shippingAddress.setAddress = function (addressData) {
        // Include the area in address data
        addressData['area'] = $('#area').val();
        this.address(addressData);
    };

    return shippingAddress;
});
