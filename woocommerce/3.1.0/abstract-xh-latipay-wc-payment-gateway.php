<?php

abstract class Abstract_XH_Latipay_Payment_Gateway extends WC_Payment_Gateway
{
    protected $instructions;

    public function __construct()
    {
        $this->id = strtolower(get_called_class());

        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->instructions = $this->get_option('instructions');

        add_filter('woocommerce_payment_gateways', array($this, 'woocommerce_add_gateway'), 10, 1);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', XH_LATIPAY),
                'type' => 'checkbox',
                'default' => 'no',
                'section' => 'default'
            ),
            'title' => array(
                'title' => __('title', XH_LATIPAY),
                'type' => 'text',
                'default' => $this->get_payment_method(),
                'desc_tip' => true,
                'css' => 'width:400px',
                'section' => 'default'
            ),
            'description' => array(
                'title' => __('description', XH_LATIPAY),
                'type' => 'textarea',
                'desc_tip' => true,
                'css' => 'width:400px',
                'section' => 'default'
            ),
            'instructions' => array(
                'title' => __('Instructions', XH_LATIPAY),
                'type' => 'textarea',
                'css' => 'width:400px',
                'description' => __('Instructions that will be added to the thank you page.', XH_LATIPAY),
                'default' => '',
                'section' => 'default'
            )
        );
    }

    public function woocommerce_add_gateway($methods)
    {
        $methods [] = $this;
        return $methods;
    }

    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wpautop(wptexturize($this->instructions));
        }
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->payment_method) {
            echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
        }
    }

    abstract function get_payment_method();

    public function process_payment($order_id)
    {
        $order = new WC_Order ($order_id);
        if (!$order || !$order->needs_payment()) {
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        $total_amount = round($order->get_total(), 2);
        $gateway = "https://api.latipay.net/v2";
        $url_return = $this->get_return_url($order);
        $url_notify = $this->get_return_url($order);
        $currency = get_woocommerce_currency();
        $supported_currencies = array('NZD', 'CNY', 'AUD');
        if (!in_array($currency, $supported_currencies)) {
            throw new Exception('Only currency:' . join(',', $supported_currencies) . ' can by allowed!');
        }

        $options = get_option('xh_latipay', array());
        $user_id = isset($options['user_id']) ? $options['user_id'] : null;
        $api_key = isset($options['api_key']) ? $options['api_key'] : null;
        $wallet_id = isset($options["wallet_id_" . strtolower($currency)]) ? $options["wallet_id_" . strtolower($currency)] : null;
        $payment_method = $this->get_payment_method();

        $_prehash = $user_id . $wallet_id . $total_amount . $payment_method . $url_return . $url_notify;
        $signature = hash_hmac('sha256', $_prehash, $api_key);

        require_once 'includes/lib/Latipay.php';
        require_once 'includes/lib/IP.php';
        $latipay = new Latipay($gateway);

        $post_data = array(
            'wallet_id' => $wallet_id,
            'amount' => $total_amount,
            'currency' => $currency,
            'user_id' => $user_id,
            'merchant_reference' => $order_id,
            'return_url' => $url_return,
            'callback_url' => $url_notify,
            'ip' => IP::clientIP(),
            'version' => '2.0',
            'product_name' => $this->get_order_title($order),
            'payment_method' => $payment_method,
            'present_qr' => '1',
            'signature' => $signature,
            'backPage_url' => $order->get_cancel_order_url(),
        );

        if ($payment_method == 'alipay') {
            $is_spotpay = isset($options['is_spotpay']) ? $options['is_spotpay'] : null;
            if ($is_spotpay && $is_spotpay == 1) {
                $post_data['is_spotpay'] = 1;
            }
        }

        if (isset($options['IS_DEBUG']) && $options['IS_DEBUG'] == 1) {
            $logFile = dirname(__FILE__) . '/latipay-debug.log';
            $logStr = date('Y-m-d H:i:s') . ': ' . json_encode($post_data) . PHP_EOL;
            file_put_contents($logFile, $logStr, FILE_APPEND);
        }

        try {
            $payment = $latipay->createPayment($post_data);
            if (isset($payment['code']) && $payment['code'] != '0') {
                throw new Exception($payment['message']);
            }
            $response_signature = hash_hmac('sha256', $payment['nonce'] . $payment['host_url'], $api_key);
            if ($response_signature == $payment['signature']) {
                return array(
                    'result' => 'success',
                    'redirect' => $payment['host_url'] . '/' . $payment['nonce']
                );

            }

        } catch (Exception $e) {
            throw new Exception('Payment failed : ' . $e->getMessage());
        }
    }

    public function get_order_title($order, $limit = 32)
    {
        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        $title = "#{$order_id}";

        $order_items = $order->get_items();
        if ($order_items) {
            $qty = count($order_items);
            foreach ($order_items as $item_id => $item) {
                $title .= "|{$item['name']}";
                break;
            }
            if ($qty > 1) {
                $title .= '...';
            }
        }

        $title = mb_strimwidth($title, 0, $limit);
        return apply_filters('xh-payment-get-order-title', $title, $order);
    }
}

?>
