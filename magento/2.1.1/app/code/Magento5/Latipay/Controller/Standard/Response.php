<?php

namespace Magento5\Latipay\Controller\Standard;

class Response extends \Magento5\Latipay\Controller\LatipayAbstract
{

    public function execute()
    {
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');
        try {
            $paymentMethod = $this->getPaymentMethod();
            $params = $this->getRequest()->getParams();
            if ($paymentMethod->validateResponse($params) || true) {
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
                $order = $this->getOrder();
                $payment = $order->getPayment();
                $paymentMethod->postProcessing($order, $payment, $params);
            } else {
                $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong.'));
        }
        $this->getResponse()->setRedirect($returnUrl);
    }

}
