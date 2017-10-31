<?php
use Tygh\Registry;

include_once ('latipay/lib/Latipay.php');
require_once('latipay/lib/IP.php');

if ( !defined('AREA') ) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    /*
    if (!empty(file_get_contents('php://input'))) {
        $reqData = json_decode(file_get_contents('php://input'), true);
    } else {
        echo "Callback Fails";exit();
    }
    */
    if (!isset($_GET['merchant_reference'])) die('Callback Fails');

    $orderId = $_GET['merchant_reference'];

    $payment_id = db_get_field("SELECT ?:orders.payment_id FROM ?:orders WHERE ?:orders.order_id = ?i", $orderId);
    if (!empty($payment_id)) {
        $processor_data = fn_get_payment_method_data($payment_id);
    } else {
        echo "No Payment ID";exit();
    }

    $user_id = trim($processor_data['processor_params']['user_id']);
    $api_key = trim($processor_data['processor_params']['api_key']);

    $payment_method = $_GET['payment_method'];
    $status = $_GET['status'];
    $currency = $_GET['currency'];
    $amount = number_format($_GET['amount'], 2);

    $signature_string = $orderId . $payment_method . $status . $currency . $amount;
    $signature = hash_hmac('sha256', $signature_string, $api_key);

    $order_info = fn_get_order_info($orderId);

    if ($amount != number_format($order_info['total'], 2)) {
        echo "Non-matched Amount";exit();
    }

    if ($mode == 'return') {

        if ($signature == $_GET['signature']) {

            if ($status == "paid") {
                $response = array();
                $response["order_status"] = 'P';
                $response["reason_text"] = 'Processed';
                fn_finish_payment($orderId, $response);
                fn_order_placement_routines('route', $orderId, false);
            } else {
                $response = array();
                $response["order_status"] = 'F';
                $response["reason_text"] = 'Failure';
                fn_finish_payment($orderId, $response);
                fn_order_placement_routines('route', $orderId, false);
            }

        } else {
            echo 'Transaction: Signature Fails!';
        }

   } elseif ($mode == 'notify') {

        if ($signature == $_GET['signature']) {

            if ($status == "paid") {
                $response = array();
                $response["order_status"] = 'P';
                $response["reason_text"] = 'Processed';
                fn_finish_payment($orderId, $response);
                echo 'OK';
            } else {
                $response = array();
                $response["order_status"] = 'F';
                $response["reason_text"] = 'Failure';
                fn_finish_payment($orderId, $response);
                echo 'FAIL';
            }

        } else {
            echo 'Transaction: Signature Fails!';
        }

    } else {
        fn_order_placement_routines('checkout_redirect');
        exit;
    }

} else {

    $gateway = "https://api.latipay.net/v2";
    $url_return = fn_url("payment_notification.return?payment=latipayv2", AREA, 'current');
    $url_notify = fn_url("payment_notification.notify?payment=latipayv2", AREA, 'current');
    $user_id = trim($processor_data['processor_params']['user_id']);
    $wallet_id = '';
    //$wallet_id = trim($processor_data['processor_params']['wallet_id']);
    if ($order_info['secondary_currency'] == 'NZD') {
        $wallet_id = 'W00000001';
    } elseif ($order_info['secondary_currency'] == 'AUD') {
        $wallet_id = 'W00000002';
    } else {
        $wallet_id = 'W00000004';
    }

    $api_key = trim($processor_data['processor_params']['api_key']);
    $merchant_reference = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id; //trim($processor_data['processor_params']['merchant_reference']);

    $supported_currencies = array('NZD','CNY','AUD');
    if (!in_array($order_info['secondary_currency'], $supported_currencies)) {
        echo 'Latipay doesn\'t support ' . $order_info['secondary_currency']; exit;
    }

    if (!empty($order_info['payment_info']['payment_method'])) {
        $payment_method = $order_info['payment_info']['payment_method'];
    } else {
        echo 'Please select payment method.'; exit;
    }
    
    $_prehash =  $user_id . $wallet_id . $order_info['total'] . $payment_method . $url_return . $url_notify;
    $signature = hash_hmac('sha256', $_prehash, $api_key);

    $latipay = new Latipay($gateway);
    $post_data = array(
        'wallet_id' => $wallet_id,
        'amount' => $order_info['total'],
        'currency' => $order_info['secondary_currency'],
        'user_id' => $user_id,
        'merchant_reference' => $merchant_reference,
        'return_url' => $url_return,
        'callback_url' => $url_notify,
        'ip' => IP::clientIP(),
        'version' => '2.0',
        'product_name' => 'Order #' . $order_id,
        'payment_method' => $payment_method, // wechat, alipay, onlineBank
        'present_qr' => '1', // wechat 
       
       
        'signature' => $signature,
    );

    try {

        $payment =  $latipay->createPayment($post_data);
        $response_signature = hash_hmac('sha256', $payment['nonce'] . $payment['host_url'], $api_key);
        if ($response_signature == $payment['signature']) {
            $redirect_url = $payment['host_url'].'/'.$payment['nonce'];
            Header("Location:$redirect_url");
        }

    } catch (\Exception $e) {

        echo 'CS Cart Error : ' . $e->getMessage();

    }

    exit;

}
