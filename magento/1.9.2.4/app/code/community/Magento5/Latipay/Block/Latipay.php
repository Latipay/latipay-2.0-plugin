<?php

class Magento5_Latipay_Block_Latipay extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getAboutUrl()
    {
        return Mage::helper('latipay')->getAboutUrl();
    }
}