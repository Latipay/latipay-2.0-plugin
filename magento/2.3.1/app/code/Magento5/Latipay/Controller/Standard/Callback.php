<?php

namespace Magento5\Latipay\Controller\Standard;

use \Magento\Framework\App\Request\InvalidRequestException;

class Callback extends \Magento5\Latipay\Controller\LatipayAbstract implements \Magento\Framework\App\CsrfAwareActionInterface
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

    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }

}
