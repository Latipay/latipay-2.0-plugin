<?php
/**
 *
 * 后台通知：由LATIPAY支付平台转发银行支付确认信息。
 *          这种方式是LATIPAY支付平台服务器与商户服务器之间进行通信的，对于持卡买家不可见
 *          参数同前台通知，有返回值
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

$order_id = $_POST['merchant_reference'];
$sql = $db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1 ");
if ($sql->num_rows) {
    $order_info = $sql->row;
} else {
    die('No Order ID');
}

$payment_method = $_POST['payment_method'];
$status = $_POST['status'];
$currency = $_POST['currency'];
$amount = $_POST['amount'];

$signature_string = $order_id . $payment_method . $status . $currency . $amount;
$signature = hash_hmac('sha256', $signature_string, $api_key);
if ($signature == $_POST['signature']) {
    if ($status == "paid") {
        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $order_status_id . "' WHERE order_id = '" . $order_id . "' ");

        //send email
        $url = HTTP_SERVER . "index.php?route=payment/latipay2/callback";
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

        die('sent');
    } else {
        die('error status');
    }
} else {
    die('access denied');
}
