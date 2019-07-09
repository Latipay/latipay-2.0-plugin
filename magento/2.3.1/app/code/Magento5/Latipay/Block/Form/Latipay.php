<?php

namespace Magento5\Latipay\Block\Form;

class Latipay extends \Magento\Payment\Block\Form
{
    /**
     * Cheque DD template
     *
     * @var string
     */
    protected $_template = 'Magento5_Latipay::form/latipay.phtml';

    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Get instructions text from config
     *
     * @return null|string
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethod();
            $this->_instructions = $method->getConfigData('instructions');
        }
        return $this->_instructions;
    }
}
