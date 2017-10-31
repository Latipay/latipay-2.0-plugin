<?php

namespace Magento5\Latipay\Model;
use Magento\Framework\Escaper;
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    protected $methodCode = \Magento5\Latipay\Model\Latipay::PAYMENT_LATIPAY_CODE;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $method;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;
	

    public function __construct(
        \Magento\Payment\Helper\Data $paymenthelper,
        Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->method = $paymenthelper->getMethodInstance($this->methodCode);
        $this->escaper = $escaper;
        $this->_storeManager = $storeManager;

    }

    public function getConfig(){
        $config = [];
        if ($this->method->isAvailable()) {
            $config['payment'][$this->methodCode]['instructions'] = $this->getInstructions();
            $config['payment'][$this->methodCode]['currency'] = $this->getCurrencyCode();
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
     * Get tooltip text from config
     *
     * @return string
     */
    protected function getTooltip()
    {
        return nl2br($this->escaper->escapeHtml($this->method->getTooltip(), ['ul', 'li', 'p', 'b', 'strong']));
    }

    /**
     * Get tooltip text from config
     *
     * @return string
     */
    protected function getCurrencyCode()
    {

        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
}
