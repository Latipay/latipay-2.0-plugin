<?php

namespace Magento5\Latipay\Block\Info;

class Latipay extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Magento5_Latipay::info/latipay.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Magento5_Latipay::info/pdf/latipay.phtml');
        return $this->toHtml();
    }
}
