<?php


namespace Magento5\Latipay\Model;

use Magento\Sales\Api\Data\TransactionInterface;

class Latipay extends \Magento\Payment\Model\Method\AbstractMethod
{

    const PAYMENT_LATIPAY_CODE = 'latipay';

    protected $_code = self::PAYMENT_LATIPAY_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento5\Latipay\Block\Form\Latipay';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento5\Latipay\Block\Info\Latipay';

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    protected $_supportedCurrencyCodes = array(
        'NZD', //New Zealand Dollar
        'AUD', //Australian Dollar
        'CNY', //China RMB
    );

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSet;

    protected $helper;
    protected $orderSender;
    protected $httpClientFactory;

    protected $storeManager;

    protected $_transactionDetailKeys = array(
        'signature',
        'order_id',
        'merchant_reference',
        'currency',
        'amount',
        'payment_method',
        'status',
    );

    /**
     * Latipay constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento5\Latipay\Helper\Latipay $helper
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento5\Latipay\Helper\Latipay $helper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    )
    {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->productFactory = $productFactory;
        $this->attributeSet = $attributeSet;
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    public function getTooltip()
    {
        return trim($this->getConfigData('tooltip'));
    }

    public function getRedirectUrl()
    {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function getReturnUrl()
    {
        return $this->helper->getUrl($this->getConfigData('return_url'));
    }

    public function getCallbackUrl()
    {
        return $this->helper->getUrl($this->getConfigData('callback_url'));
    }

    public function getAboutUrl()
    {
        return $this->helper->getUrl($this->getConfigData('about_url'));
    }

    /**
     * Return url according to environment
     * @return string
     */
    public function getTransactionUrl()
    {
        $env = $this->getConfigData('environment');
        if ($env === 'production') {
            return $this->getConfigData('production_url');
        }
        return $this->getConfigData('sandbox_url');
    }

    public function getWalletUrl()
    {
        $env = $this->getConfigData('environment');
        if ($env === 'production') {
            return $this->getConfigData('wallet_url');
        }
        return $this->getConfigData('staging_wallet_url');
    }


    public function getRequestUrl()
    {
        $apiKey = $this->getConfigData('api_key');
        $responseData = $this->getTransactionData();
        if ($responseData && !empty($responseData['host_url']) && !empty($responseData['nonce'])) {
            $responseSignature = hash_hmac('sha256', $responseData['nonce'] . $responseData['host_url'], $apiKey);
            if ($responseSignature == $responseData['signature']) {
                $redirectUrl = $responseData['host_url'] . '/' . $responseData['nonce'];
                return $redirectUrl;
            }
        }

        if ($responseData && !empty($responseData['message'])) {
            die($responseData['message']);
        }

        die(__('Transaction failure'));

    }

    public function getTransactionData()
    {
        $transactionUrl = $this->getTransactionUrl();
        $orderData = json_encode($this->getOrderData());
        return $this->helper->callApi($transactionUrl,$orderData);
    }

    public function getWalletData($walletId, $userId)
    {
        $url = $this->getWalletUrl();
        return $this->helper->callGetApi($url . '/' . $walletId . '?' . 'user_id');
    }

    public function getOrderData()
    {
        $order = $this->checkoutSession->getLastOrder();
        $apiKey = $this->getConfigData('api_key');

        $data = array();
        $data['user_id'] = $this->getConfigData('user_id');
        $data['wallet_id'] = $this->getConfigData('wallet_id');
        $data['amount'] = $order->getBaseGrandTotal();
        $data['payment_method'] = $order->getPayment()->getAdditionalInformation('latipay_method') ? $order->getPayment()->getAdditionalInformation('latipay_method') : 'wechat';
        $data['return_url'] = $this->getReturnUrl();
        $data['callback_url'] = $this->getCallbackUrl();

        $sign = "";
        foreach ($data as $key => $value) {
            $sign .= $value;
        }

        $data['signature'] = hash_hmac('sha256', $sign, $apiKey);
        $data['merchant_reference'] = $order->getIncrementId();
        $data['currency'] = $this->storeManager->getStore()->getBaseCurrencyCode();
        $data['ip'] = $order->getRemoteIp();
        $data['version'] = "2.0";
        $data['product_name'] = $this->storeManager->getStore()->getFrontendName() . ' Order #' . $order->getIncrementId();
        if ($data['payment_method'] == "wechat") {
            $data['present_qr'] = 1;
        }

        return $data;
    }

    public function validateResponse($response){
        $apiKey = $this->getConfigData('api_key');
        $signature = $response['signature'];
        $merchantReference = $response['merchant_reference'];
        $currency = $response['currency'];
        $amount = $response['amount'];
        $paymentMethod = $response['payment_method'];
        $status = $response['status'];
        $signText = $merchantReference . $paymentMethod . $status . $currency . $amount;
        $callbackSignature = hash_hmac('sha256', $signText, $apiKey);
        if ($signature == $callbackSignature) {
            return true;
        }
        return false;
    }

    public function postProcessing(\Magento\Sales\Model\Order $order,
                                   \Magento\Framework\DataObject $payment, $response)
    {
        if(!empty($response['order_id'])){
            $payment->setTransactionId($response['order_id']);
        }
        foreach ($this->_transactionDetailKeys as $key) {
            isset($response[$key]) and $payment->setTransactionAdditionalInfo($key, $response[$key]);
        }
        $payment->addTransaction(TransactionInterface::TYPE_ORDER);
        $payment->setIsTransactionClosed(0);
        $payment->place();

        if (!empty($response['status']) && $response['status'] == 'paid') {
            if ($order->canInvoice()) {
                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->register();
                if ($order->getPayment()->getMethodInstance()->canCapture()) {
                    $invoice->capture();
                }
                $invoice->save();
                $transactionSave = $this->_transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $order->setStatus($order::STATE_PROCESSING);
            }
        }

        $order->addStatusHistoryComment(__("Latipay Response : %1", json_encode($response)));
        $order->save();
    }

}
