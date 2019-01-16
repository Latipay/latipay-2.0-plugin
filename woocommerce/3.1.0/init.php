<?php
/*
 * Plugin Name: Latipay For Woo
 * Plugin URI: https://www.latipay.net/
 * Description: 
 * Author: latipay
 * Version: 2.0.1
 * Author URI:  https://www.latipay.net/
 */

if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly

if (!defined('XH_LATIPAY')) {
    define('XH_LATIPAY', 'latipay');
} else {
    return;
}

define('XH_LATIPAY_VERSION', '1.0.4');
define('XH_LATIPAY_FILE', __FILE__);
define('XH_LATIPAY_DIR', rtrim(plugin_dir_path(XH_LATIPAY_FILE), '/'));
define('XH_LATIPAY_URL', rtrim(plugin_dir_url(XH_LATIPAY_FILE), '/'));
load_plugin_textdomain(XH_LATIPAY, false, dirname(plugin_basename(XH_LATIPAY_FILE)) . '/lang/');
add_action('plugin_action_links_' . plugin_basename(XH_LATIPAY_FILE), function ($links) {
    return array_merge(array(
        'settings' => '<a href="' . admin_url('options-general.php?page=xh_latipay') . '">' . __('Settings', XH_LATIPAY_FILE) . '</a>'
    ), $links);
}, 10, 1);
add_action('admin_menu', function () {
    add_options_page(
        __('Latipay', XH_LATIPAY),
        __('Latipay', XH_LATIPAY),
        'administrator',
        'xh_latipay',
        function () {
            $options = get_option('xh_latipay', array());
            $user_id = isset($options['user_id']) ? $options['user_id'] : null;
            $api_key = isset($options['api_key']) ? $options['api_key'] : null;
            $error = null;
            if (!empty($user_id) && !empty($api_key)) {
                foreach (array('cny', 'aud', 'nzd') as $currency) {
                    $wallet_id = isset($options["wallet_id_{$currency}"]) ? $options["wallet_id_{$currency}"] : null;
                    if (!empty($wallet_id)) {
                        $sign = hash_hmac('sha256', $wallet_id . $user_id, $api_key);
                        $uri = "https://api.latipay.net/v2/detail/{$wallet_id}?user_id={$user_id}&signature={$sign}";

                        if (!class_exists('Latipay')) {
                            require_once 'includes/lib/Latipay.php';
                            require_once 'includes/lib/IP.php';
                        }

                        $client = new RestClient();
                        $result = json_decode($response = $client->url($uri)->get(), true);
                        if ($result['code'] == '0') {
                            update_option("xh_latipay_payment_$currency", explode(',', $result['payment_method']));
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
            }

            ?>
            <form method="post" id="mainform" action="options.php" enctype="multipart/form-data">
                <h3><?php echo __('Latipay settings', XH_LATIPAY) ?></h3>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout') ?>">Go woo settings</a>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><label>User ID</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['user_id']) ? $options['user_id'] : null) ?>"
                                   name="xh_latipay[user_id]"/>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>Api key</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['api_key']) ? $options['api_key'] : null) ?>"
                                   name="xh_latipay[api_key]"/>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <h5>Wallet ID for currency</h5>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>CNY</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['wallet_id_cny']) ? $options['wallet_id_cny'] : null) ?>"
                                   name="xh_latipay[wallet_id_cny]"/>
                            <div style="color:gray"><?php
                                $methods = get_option("xh_latipay_payment_cny", array());
                                echo is_array($methods) ? join(',', $methods) : null;
                                ?></div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>NZD</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['wallet_id_nzd']) ? $options['wallet_id_nzd'] : null) ?>"
                                   name="xh_latipay[wallet_id_nzd]"/>
                            <div style="color:gray"><?php
                                $methods = get_option("xh_latipay_payment_nzd", array());
                                echo is_array($methods) ? join(',', $methods) : null;
                                ?></div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>AUD</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['wallet_id_aud']) ? $options['wallet_id_aud'] : null) ?>"
                                   name="xh_latipay[wallet_id_aud]"/>
                            <div style="color:gray"><?php
                                $methods = get_option("xh_latipay_payment_aud", array());
                                echo is_array($methods) ? join(',', $methods) : null;
                                ?></div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>is_spotpay</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['is_spotpay']) ? $options['is_spotpay'] : null) ?>"
                                   name="xh_latipay[is_spotpay]"/>
                            <div style="color:gray">
                                default empty, do not change, unless you’ve been told to.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>IS_DEBUG</label></th>
                        <td>
                            <input type="text" style="width:400px;"
                                   value="<?php echo esc_attr(isset($options['IS_DEBUG']) ? $options['IS_DEBUG'] : 0) ?>"
                                   name="xh_latipay[IS_DEBUG]"/>
                        </td>
                    </tr>

                    </tbody>
                </table>
                <p class="submit">
                    <?php
                    wp_nonce_field('update-options')
                    ?>
                    <input type="hidden" name="action" value="update"/>
                    <input type="hidden" name="page_options" value="xh_latipay"/>
                    <input type="submit" value="<?php echo __('Save', XH_LATIPAY) ?>" class="button-primary"/>
                </p>
            </form>
            <?php
        });
});

add_action('init', function () {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    $currency = strtolower(get_woocommerce_currency());
    $methods = get_option("xh_latipay_payment_{$currency}", array());

    require_once 'abstract-xh-latipay-wc-payment-gateway.php';
    if ($methods && is_array($methods)) {
        foreach ($methods as $method) {
            switch (strtolower(trim($method))) {
                case 'alipay':
                    require_once 'class-latipay-alipay-wc-payment-gateway.php';
                    new XHLatipayAlipayForWC();
                    break;

                case 'wechat':
                    require_once 'class-latipay-wechat-wc-payment-gateway.php';
                    new XHLatipayWechatForWC();
                    break;

                case 'payease':
                    require_once 'class-latipay-payease-wc-payment-gateway.php';
                    new XHLatipayPayeaseForWC();
                    break;

                case 'onlinebank':
                    require_once 'class-latipay-payease-wc-payment-gateway.php';
                    new XHLatipayPayeaseForWC();
                    break;
            }
        }
    }

    $post_data = $_REQUEST;
    if (
        !isset($post_data['merchant_reference'])
        || !isset($post_data['payment_method'])
        || !isset($post_data['status'])
        || !isset($post_data['currency'])
        || !isset($post_data['amount'])
        || !isset($post_data['signature'])
    ) {
        return;
    }

    $options = get_option('xh_latipay', array());

    if (isset($options['IS_DEBUG']) && $options['IS_DEBUG'] == 1) {
        $logFile = dirname(__FILE__) . '/latipay-debug.log';
        $logStr = date('Y-m-d H:i:s') . ': ' . json_encode($post_data) . PHP_EOL;
        file_put_contents($logFile, $logStr, FILE_APPEND);
    }

    $api_key = isset($options['api_key']) ? $options['api_key'] : null;
    $orderId = $post_data['merchant_reference'];
    $payment_method = $post_data['payment_method'];
    $status = $post_data['status'];
    $currency = $post_data['currency'];
    $amount = $post_data['amount'];

    $signature_string = $orderId . $payment_method . $status . $currency . $amount;
    $signature = hash_hmac('sha256', $signature_string, $api_key);
    if ($signature != $post_data['signature']) {
        return;
    }

    $order = new WC_Order($orderId);
    if ($status == "paid") {
        $order->payment_complete(isset($post_data['transaction_id']) ? $post_data['transaction_id'] : null);
        if (isset($_POST['merchant_reference']) && isset($_POST['status'])) {
            die('sent');
        }
    }
    return;
});
