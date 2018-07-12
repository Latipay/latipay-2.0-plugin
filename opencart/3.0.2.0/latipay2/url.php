<?php
error_reporting(E_ALL); //E_ALL
 
function cache_shutdown_error() {
 
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
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/5/16
 * Time: 10:07
 *
 * 前台通知：将此次支付订单的交易结果（参数）以页面连接的形式发送给商户。这里的前台指这个参数传递过程对持卡买家是可见的，无返回值
 * 详细说明见API文档 https://merchant.latipay.co.nz/developer/api.action
 */

//http://www.ccxshop.cn/latipay_demo/url.php?orderId=20161125-M00001263-1&payType=2&status=20&currency=NZD&amount=0.01&md5info=e5292ebc945cedf4be822b6fad27316b
	
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
foreach($query->rows as $result){
	$config->set($result['key'], $result['value']);
}

$api_key = trim($config->get('payment_latipay2_api_key'));
$user_id = trim($config->get('payment_latipay2_user_id'));
$wallet_id = trim($config->get('payment_latipay2_wallet_id'));


//支付成功后的订单状态
$order_status_id = $config->get('payment_latipay2_order_status_id');


//http://oc.hkbjc.com/latipay2/url.php?
//merchant_reference=41&payment_method=alipay&status=paid&currency=NZD&amount=0.01&signature=86c1f12d0f96d2d434973eddc212cf5daf7bf11bcd4d605da035d3daa43acd98
$file  = 'latipay2_url_log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个

$postStr = $_POST ? json_encode($_POST) : '';
$getStr = $_GET ? json_encode($_GET) : '';
$logStr = date('Y-m-d H:i:s') . ' POST CALLBACK : ' . $postStr . PHP_EOL ;
$logStr .= date('Y-m-d H:i:s') . ' GET CALLBACK : ' . $getStr . PHP_EOL;
file_put_contents($file , $logStr , FILE_APPEND);

$order_id = $_GET['merchant_reference'];


//查看是否存在此order ID
$sql = $db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '".$order_id."' LIMIT 1 ");
if ($sql->num_rows) {
	$order_info = $sql->row;
} else {
	echo "No Order ID";exit();
}


$payment_method = $_GET['payment_method'];
$status = $_GET['status'];
$currency = $_GET['currency'];
$amount = $_GET['amount'];


/*if ($amount != number_format($order_info['total'], 2)) {
	echo "Non-matched Amount";exit();
}*/

$signature_string = $order_id . $payment_method . $status . $currency . $amount;
//41alipaypaidNZD0.01
//echo $signature_string;


$signature = hash_hmac('sha256', $signature_string, $api_key);
//echo $signature;

 if ($signature == $_GET['signature']) {

	if ($status == "paid") {
		
		
		//修改订单状态
		$db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '".$order_status_id."' WHERE order_id = '".$order_id."' ");
		
		//发送邮件信息
		//发送邮件
		//210.5.2.106
		$url = HTTP_SERVER ."index.php?route=extension/payment/latipay2/callback";
		
		$post_data = array (
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
		
		header("Location: ".HTTP_SERVER."index.php?route=checkout/success");
		exit;
		
		
	} else {
		echo 'Transaction: Signature Fails!';
		
		$content = 'Transaction: Signature Fails22!';
		
		file_put_contents($file , $content . $log , FILE_APPEND);
	}

} else {
	
	$content = 'Transaction: Signature Fails!';
		
	file_put_contents($file , $content . $log , FILE_APPEND);
	
	echo 'Transaction: Signature Fails!';
}
