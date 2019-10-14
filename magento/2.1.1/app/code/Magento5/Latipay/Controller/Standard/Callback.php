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

                $merchantOrderId = substr($params['merchant_reference'], 0, strripos($params['merchant_reference'], '_'));
                $order = $this->getOrderById($merchantOrderId);
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
