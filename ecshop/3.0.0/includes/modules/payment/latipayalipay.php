<?php
/**
 * Latipay V2
 */

if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/latipayalipay.php';
if (file_exists($payment_lang)) {
    global $_LANG;
    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = "latipayalipay";

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'latipayalipay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';

    /* 作者 */
    $modules[$i]['author'] = 'latipay support';

    /* 网址 */
    $modules[$i]['website'] = 'https://www.latipay.net/';

    /* 版本号 */
    $modules[$i]['version'] = '2.0.1';

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'latipayalipay_mchid', 'type' => 'text', 'value' => ''),
        array('name' => 'latipayalipay_key', 'type' => 'text', 'value' => ''),
        array('name' => 'latipayalipay_walletid', 'type' => 'text', 'value' => ''),
        array('name' => 'latipayalipay_is_spotpay', 'type' => 'text', 'value' => '0'),);
    return;
}

/**
 * latipay 支付类
 */
class latipayalipay
{

    var $parameters;
    // cft 参数
    var $payment;
    // 配置信息
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */


    /**
     * 生成支付代码
     * @param   array $order_latipay 订单信息 对于latipay特殊处理
     * @param   array $payment 支付方式信息
     */


    function get_code($order_latipay, $payment)
    {
        if (!defined('EC_CHARSET')) {
            $charset = 'utf-8';
        } else {
            $charset = EC_CHARSET;
        }

        //为respond做准备
        $this->payment = $payment;
        $charset = strtoupper($charset);

        $root = $GLOBALS['ecs']->url();

        $walletid = $payment['latipayalipay_walletid'];
        $merId = $payment['latipayalipay_mchid'];
        //apikey
        $ikey = $payment['latipayalipay_key'];

        $data = array(
            'user_id' => $merId,
            'wallet_id' => $walletid,
            'amount' => $order_latipay['order_amount'],
            'payment_method' => 'alipay',
            'return_url' => $root . "latipayrespond.php",
            'callback_url' => $root . "latipaycallback.php",
            //'backPage_url' => '',
            'merchant_reference' => date('Ymd') . '-' . $merId . '-' . ($order_latipay['order_sn']) . '_' . uniqid(),
            'ip' => real_ip(),
            'product_name' => $order_latipay['order_sn'],
            'version' => '2.0',
        );

        ksort($data);
        $item = array();
        foreach ($data as $key => $value) {
            $item[] = $key . "=" . $value;
        }
        $_prehash =  join("&", $item);
        $signature = hash_hmac('sha256', $_prehash . $ikey, $ikey);
        $data['signature'] = $signature;

        $url = 'https://api.latipay.net/v2/transaction/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        header('Location:' . $result['host_url'] . '/' . $result['nonce']);
        die;

    }

    /**
     * 响应操作--向latipay查询是否付款成功
     * @param $payment
     * @return bool
     */
    function respond($payment)
    {
        $order_latipayId = $_GET["merchant_reference"];
        $payment_method = $_GET['payment_method'];
        $status = $_GET['status'];
        $currency = $_GET['currency'];
        $amount = $_GET['amount'];
        $api_key = $payment['latipayalipay_key'];

        $signature_string = $order_latipayId . $payment_method . $status . $currency . $amount;
        $signature = hash_hmac('sha256', $signature_string, $api_key);

        if ($signature == $_GET['signature']) {
            if ($status == "paid") {
                $order_id = substr($order_latipayId, 0, strripos($order_latipayId, '_'));
                $order_latipay_sn = explode('-', $order_id);
                //循环订单分别确认付款
                for ($i = 2; $i < count($order_latipay_sn); $i++) {
                    $order_sn = $order_latipay_sn[$i];

                    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '" . $order_sn . "'";
                    $order_st = $GLOBALS['db']->getRow($sql);

                    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id = '" . $order_st['order_id'] . "'";
                    $pay_log = $GLOBALS['db']->getRow($sql);

                    //获取log_id
                    $log_id = $pay_log['log_id'];

                    order_paid($log_id, 2);
                }

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 服务端异步回调确认
     * @param $payment
     * @return bool
     */
    function callback_confirm($payment)
    {
        $order_latipayId = $_POST["merchant_reference"];
        $payment_method = $_POST['payment_method'];
        $status = $_POST['status'];
        $currency = $_POST['currency'];
        $amount = $_POST['amount'];
        $api_key = $payment['latipayalipay_key'];

        $signature_string = $order_latipayId . $payment_method . $status . $currency . $amount;
        $signature = hash_hmac('sha256', $signature_string, $api_key);

        if ($signature == $_POST['signature']) {
            if ($status == "paid") {
                $order_id = substr($order_latipayId, 0, strripos($order_latipayId, '_'));
                $order_latipay_sn = explode('-', $order_id);
                //循环订单分别确认付款
                for ($i = 2; $i < count($order_latipay_sn); $i++) {
                    $order_sn = $order_latipay_sn[$i];

                    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '" . $order_sn . "'";
                    $order_st = $GLOBALS['db']->getRow($sql);

                    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id = '" . $order_st['order_id'] . "'";
                    $pay_log = $GLOBALS['db']->getRow($sql);

                    //获取log_id
                    $log_id = $pay_log['log_id'];

                    order_paid($log_id, 2);
                }

                return true;
            } else {
                return false;
            }
        }
    }

}

?>
