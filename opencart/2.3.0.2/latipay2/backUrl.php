<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/25
 * Time: 10:40
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


//支付成功后的订单状态
$order_status_id = $config->get('latipay2_order_status_id');


$file  = 'latipay2_back_url_log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一

$postStr = $_POST ? json_encode($_POST) : '';
$getStr = $_GET ? json_encode($_GET) : '';
$logStr = date('Y-m-d H:i:s') . ' POST CALLBACK : ' . $postStr . PHP_EOL ;
$logStr .= date('Y-m-d H:i:s') . ' GET CALLBACK : ' . $getStr . PHP_EOL;
file_put_contents($file , $logStr , FILE_APPEND);

$order_id = $_POST['merchant_reference'];


//查看是否存在此order ID
$sql = $db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '".$order_id."' LIMIT 1 ");
if ($sql->num_rows) {
    $order_info = $sql->row;
} else {
    echo "No Order ID";
    exit();
}


$payment_method = $_POST['payment_method'];
$status = $_POST['status'];
$currency = $_POST['currency'];
$amount = $_POST['amount'];


/*if ($amount != number_format($order_info['total'], 2)) {
    echo "Non-matched Amount";exit();
}*/

$signature_string = $order_id . $payment_method . $status . $currency . $amount;
$signature = hash_hmac('sha256', $signature_string, $api_key);


 if ($signature == $_POST['signature']) {
     if ($status == "paid") {
        
        
        //修改订单状态
        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '".$order_status_id."' WHERE order_id = '".$order_id."' ");
        
        //发送邮件信息
        //发送邮件
        //210.5.2.106
        $url = HTTP_SERVER ."index.php?route=extension/payment/latipay2/callback";
        
         $post_data = array(
            "order_id" => $order_id,
            "order_status_id" => $order_status_id
        );
        
         $ch = curl_init();
        
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
         $output = curl_exec($ch);
         curl_close($ch);
        
         echo 'sent';
         $content = 'sent';
         file_put_contents($file, $content . $log, FILE_APPEND);
         exit;
     } else {
         echo 'error2';
        
         $content = 'error2';
        
         file_put_contents($file, $content . $log, FILE_APPEND);
     }
 } else {
     echo 'error';
    
     $content = 'error';
    
     file_put_contents($file, $content . $log, FILE_APPEND);
 }
