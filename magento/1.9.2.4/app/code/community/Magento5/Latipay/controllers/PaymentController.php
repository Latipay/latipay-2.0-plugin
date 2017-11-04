<?php

class Magento5_Latipay_PaymentController extends Mage_Core_Controller_Front_Action
{
    protected $_transactionDetailKeys = array(
        'signature',
        'transaction_id',
        'merchant_reference',
        'currency',
        'amount',
        'payment_method',
        'status',
    );

    public function redirectAction()
    {
        $apiKey = Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/latipay/api_key'));
        $responseData = Mage::helper('latipay')->getTransactionData();
        if ($responseData && !empty($responseData['host_url']) && !empty($responseData['nonce'])) {
            $responseSignature = hash_hmac('sha256', $responseData['nonce'] . $responseData['host_url'], $apiKey);
            if ($responseSignature == $responseData['signature']) {
                $redirectUrl = $responseData['host_url'] . '/' . $responseData['nonce'];
                $this->_redirectUrl($redirectUrl);
                return;
            }
        }

        if ($responseData && !empty($responseData['message'])) {
            die($responseData['message']);
        }

        die(__('Transaction failure'));
    }

    public function returnAction()
    {
        $this->_responseProcessing('return');
    }

    public function callbackAction()
    {
        $this->_responseProcessing('callback');
    }

    public function _responseProcessing($type = 'return')
    {
        $apiKey = Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/latipay/api_key'));

        $signature = $this->getRequest()->getParam('signature');
        $transactionId = $this->getRequest()->getParam('order_id');
        $merchantReference = $this->getRequest()->getParam('merchant_reference');
        $currency = $this->getRequest()->getParam('currency');
        $amount = $this->getRequest()->getParam('amount');
        $paymentMethod = $this->getRequest()->getParam('payment_method');
        $status = $this->getRequest()->getParam('status');

        $signText = $merchantReference . $paymentMethod . $status . $currency . $amount;
        $callbackSignature = hash_hmac('sha256', $signText, $apiKey);
        $postData = $this->getRequest()->getParams();

        if ($signature == $callbackSignature) {
            if ($status == "paid") {
                $order = Mage::getModel('sales/order')->loadByIncrementId($merchantReference);
                $payment = $order->getPayment();
                $payment->setTransactionId($transactionId);

                foreach ($this->_transactionDetailKeys as $key) {
                    isset($postData[$key]) and $payment->setTransactionAdditionalInfo($key, $postData[$key]);
                }

                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                    $invoice->register();
                    $invoice->save();
                    $order->addStatusHistoryComment(Mage::helper('core')->__('Invoice #%s created', $invoice->getIncrementId()), false)->setIsCustomerNotified(false);
                }

                $order->addStatusHistoryComment(Mage::helper('core')->__('Payment successful'), false)->setIsCustomerNotified(false);

                try {
                    $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
                    $status = true;
                    $order->setState($state, $status);
                    $order->save();
                    Mage::getSingleton('checkout/session')->unsQuoteId();
                    
                    if ($type == 'return') {
                        $this->_redirect('checkout/onepage/success', array('_secure' => false));
                    } else {
                        die('success');
                    }
                } catch (Exception $e) {
                }

            } else {
                if ($type == 'return') {
                    $this->_redirect('checkout/onepage/error', array('_secure' => true));
                } else {
                    die('fail');
                }
            }
        } else {
            if ($type == 'return') {
                $this->_redirect('checkout/onepage/error', array('_secure' => true));
            } else {
                die('fail');
            }
        }

    }

}