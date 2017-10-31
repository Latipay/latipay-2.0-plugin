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
                $order = $this->getOrder();
                $payment = $order->getPayment();
                $paymentMethod->postProcessing($order, $payment, $params);
                die('success');
            } else {
                //$this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //$this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            //$this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }
        die('fail');
    }

}
