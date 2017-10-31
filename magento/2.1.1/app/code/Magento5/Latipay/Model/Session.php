<?php
namespace Magento5\Latipay\Model;

class Session extends \Magento\Checkout\Model\Session
{

    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastOrder()
    {
        $orderId = $this->getLastOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getId()) {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId) {
            $this->_order->load($orderId);
        }
        return $this->_order;
    }

}