define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],function(Component,renderList){
    'use strict';
    renderList.push({
        type : 'latipay',
        component : 'Magento5_Latipay/js/view/payment/method-renderer/latipay-method'
    });

    return Component.extend({});
})
