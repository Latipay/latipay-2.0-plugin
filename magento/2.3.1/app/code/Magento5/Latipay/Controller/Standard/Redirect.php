<?php

namespace Magento5\Latipay\Controller\Standard;

class Redirect extends \Magento5\Latipay\Controller\LatipayAbstract
{

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
            );
        }

        $quote = $this->getQuote();
        $email = $this->getRequest()->getParam('email');
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        } else {
            $quote->setCustomerEmail($email);
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        } else {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        }

        $quote->setCustomerEmail($email);
        $quote->save();

        $params = [];
        $params["url"] = $this->getPaymentMethod()->getRequestUrl();
        return $this->resultJsonFactory->create()->setData($params);
    }

}
