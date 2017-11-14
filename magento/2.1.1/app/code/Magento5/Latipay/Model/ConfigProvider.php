<?php

namespace Magento5\Latipay\Model;

use Magento\Framework\Escaper;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    protected $methodCode = \Magento5\Latipay\Model\Latipay::PAYMENT_LATIPAY_CODE;
    
    
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;
    

    public function __construct(
        \Magento\Payment\Helper\Data $paymenthelper,
        Escaper $escaper
    ) {
        $this->method = $paymenthelper->getMethodInstance($this->methodCode);
        $this->escaper = $escaper;
    }

    public function getConfig()
    {
        $config = [];
        if ($this->method->isAvailable()) {
            $config['payment'][$this->methodCode]['instructions'] = $this->getInstructions();
            $config['payment'][$this->methodCode]['walletdata'] = $this->getWalletData();
            $config['payment'][$this->methodCode]['tooltip'] = $this->getTooltip();
            $config['payment'][$this->methodCode]['redirectUrl'] = $this->method->getRedirectUrl();
        }

        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    protected function getInstructions()
    {
        return nl2br($this->escaper->escapeHtml($this->method->getInstructions(), ['ul', 'li', 'p', 'b', 'strong']));
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    protected function getWalletData()
    {
        return $this->method->getWalletData();
    }

    /**
     * Get tooltip text from config
     *
     * @return string
     */
    protected function getTooltip()
    {
        return nl2br($this->escaper->escapeHtml($this->method->getTooltip(), ['ul', 'li', 'p', 'b', 'strong']));
    }
}
