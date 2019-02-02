<?php
error_reporting(E_ALL); //E_ALL

function cache_shutdown_error()
{

    $_error = error_get_last();
    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {

        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}

register_shutdown_function("cache_shutdown_error");
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
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE code = 'payment_latipay2' ");
foreach ($query->rows as $result) {
    $config->set($result['key'], $result['value']);
}

$api_key = trim($config->get('payment_latipay2_api_key'));
$user_id = trim($config->get('payment_latipay2_user_id'));
$wallet_id = trim($config->get('payment_latipay2_wallet_id'));

$order_status_id = $config->get('payment_latipay2_order_status_id');
$order_id = $_GET['merchant_reference'];

$sql = $db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1 ");
if ($sql->num_rows) {
    $order_info = $sql->row;
} else {
    die('No Order ID');
}

$payment_method = $_GET['payment_method'];
$status = $_GET['status'];
$currency = $_GET['currency'];
$amount = $_GET['amount'];

$signature_string = $order_id . $payment_method . $status . $currency . $amount;
$signature = hash_hmac('sha256', $signature_string, $api_key);
if ($signature == $_GET['signature']) {
    if ($status == "paid") {
        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $order_status_id . "' WHERE order_id = '" . $order_id . "' ");

        $url = HTTP_SERVER . "index.php?route=extension/payment/latipay2/callback";
        $post_data = array(
            "order_id" => $order_id,
            "order_status_id" => $order_status_id
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
