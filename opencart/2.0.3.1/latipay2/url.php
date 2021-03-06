<?php
/**
 *
 * 前台通知：将此次支付订单的交易结果（参数）以页面连接的形式发送给商户。这里的前台指这个参数传递过程对持卡买家是可见的，无返回值
 * 详细说明见API文档 https://merchant.latipay.co.nz/developer/api.action
 */

require_once('../config.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE code = 'latipay2' ");
foreach ($query->rows as $result) {
    $config->set($result['key'], $result['value']);
}

$api_key = trim($config->get('latipay2_api_key'));
$user_id = trim($config->get('latipay2_user_id'));
$wallet_id = trim($config->get('latipay2_wallet_id'));
$order_status_id = $config->get('latipay2_order_status_id');

$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$currency = isset($_GET['currency']) ? $_GET['currency'] : '';
$amount = isset($_GET['amount']) ? $_GET['amount'] : '';
$merchant_order_id = isset($_GET['merchant_reference']) ? $_GET['merchant_reference'] : '';

$signature_string = $merchant_order_id . $payment_method . $status . $currency . $amount;
$signature = hash_hmac('sha256', $signature_string, $api_key);
if ($signature == $_GET['signature']) {

    $order_id = substr($merchant_order_id, 0, strripos($merchant_order_id, '_'));
    $sql = $db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1 ");
    if (!$sql->num_rows) {
        die('No Order ID');
    }

    if ($status == "paid") {
        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $order_status_id . "' WHERE order_id = '" . $order_id . "' ");

        //send email
        $url = HTTP_SERVER . "index.php?route=payment/latipay2/callback";
        $post_data = array(
            "order_id" => $order_id,
            "order_status_id" => $order_status_id,
            "latipay_order_id" => isset($_GET['order_id']) ? $_GET['order_id'] : '',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        header("Location: " . HTTP_SERVER . "index.php?route=checkout/success");
        exit;
    } else {
        die('error status');
    }
} else {
    die('Transaction: Signature Fails!');
}
