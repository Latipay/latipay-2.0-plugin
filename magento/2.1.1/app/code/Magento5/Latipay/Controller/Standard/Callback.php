<?php

namespace Magento5\Latipay\Controller\Standard;

class Callback extends \Magento5\Latipay\Controller\LatipayAbstract
{

    public function execute()
    {
        try {
            $paymentMethod = $this->getPaymentMethod();
            $params = $this->getRequest()->getParams();

            if ($paymentMethod->validateResponse($params)) {
                $order = $this->getOrderById($params['merchant_reference']);
                $payment = $order->getPayment();

                $paymentMethod->postProcessing($order, $payment, $params);
                die('sent');
            }
        } catch (\Exception $e) {
            die('error');
        }
        
        die('fail');
    }

}
