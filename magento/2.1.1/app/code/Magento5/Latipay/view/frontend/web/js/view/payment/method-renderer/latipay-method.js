define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento5_Latipay/js/action/set-payment-method',
        'jquery'
    ],
    function(Component,setPaymentMethod,$){
    'use strict';

    return Component.extend({
        defaults:{
            'template':'Magento5_Latipay/payment/latipay',
            latipayMethod: 'wechat'
        },
        initObservable: function () {
            this._super()
                .observe('latipayMethod');

            return this;
        },
        getInstructions: function () {
            return window.checkoutConfig.payment.latipay.instructions;
        },
        getCurrencyCode: function () {
            return window.checkoutConfig.payment.latipay.currency;
        },
        getTooltip: function () {
            if (window.checkoutConfig.payment.latipay.tooltip.length > 0) {
                $('.tooltip-latipay').show();
            } else {
                $('.tooltip-latipay').hide();
            }
            return window.checkoutConfig.payment.latipay.tooltip;
        },
        getData: function () {
            return {
                "method": this.item.method,
                "additional_data": {
                    'latipay_method': $("input[name='payment[latipay_method]']:checked").val()
                }
            };

        },
        redirectAfterPlaceOrder: false,
        
        afterPlaceOrder: function () {
            setPaymentMethod();    
        }

    });
});
