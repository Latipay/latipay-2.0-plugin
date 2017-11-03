<?php

class Magento5_Latipay_Block_Info_Latipay extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $data = array();
        $data['Method'] = $this->getMethod()->getInfoInstance()->getLatipayMethod();
        $transport = parent::_prepareSpecificInformation($transport);
        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
