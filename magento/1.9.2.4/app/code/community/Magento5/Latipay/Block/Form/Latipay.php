<?php

class Magento5_Latipay_Block_Form_Latipay extends Mage_Payment_Block_Form
{
    protected function _construct()
    {

        $mark = Mage::getConfig()->getBlockClassName('latipay/latipay');
        $mark = new $mark;
        $mark->setTemplate('magento5/latipay/mark.phtml');

        parent::_construct();
        $this->setTemplate('magento5/latipay/form/latipaypaymentmethod.phtml')
        	->setMethodTitle('')
            ->setMethodLabelAfterHtml($mark->toHtml());
    }

}

