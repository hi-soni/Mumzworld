define([
    'Magento_Checkout/js/view/shipping'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mumzworld_ExerciseThree/shipping'
        },

        initialize: function () {
            this._super();
        },

        getArea: function () {
            return this.address().customAttributes.area || '';
        }
    });
});
