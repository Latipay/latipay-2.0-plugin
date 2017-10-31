<?php

class Magento5_Latipay_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'latipay';
    protected $_formBlockType = 'latipay/form_latipay';
    protected $_infoBlockType = 'latipay/info_latipay';

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setLatipayMethod($data->getLatipayMethod());
        return $this;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('latipay/payment/redirect', array('_secure' => false));
    }

    public function canUseForCurrency($currencyCode)
    {
        return Mage::helper('latipay')->canUseCurrency($currencyCode);
    }


}
