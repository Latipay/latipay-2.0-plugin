<?php
/* Latipay支付插件
 * ============================================================================
 * *
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: latipay.php $
 */

if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/latipaywechat.php';

if (file_exists($payment_lang)) {
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = "latipaywechat";

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'latipaywechat_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';

    /* 作者 */
    $modules[$i]['author'] = 'Max';

    /* 网址 */
    $modules[$i]['website'] = 'https://www.latipay.net/';

    /* 版本号 */
    $modules[$i]['version'] = '2.0.0';

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'latipaywechat_mchid', 'type' => 'text', 'value' => ''),
        array('name' => 'latipaywechat_key', 'type' => 'text', 'value' => ''),
        array('name' => 'latipaywechat_walletid', 'type' => 'text', 'value' => ''),);
    return;
}

/**
 * latipay 支付类
 */
class latipaywechat
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

        $walletid = $payment['latipaywechat_walletid'];
        $merId = $payment['latipaywechat_mchid'];
        //秘钥
        $ikey = $payment['latipaywechat_key'];
        // apikey
        $toSubmit['user_id'] = $merId;
        //user_id
        $toSubmit['wallet_id'] = $walletid;
        //wallet_id
        $toSubmit['amount'] = $order_latipay['order_amount'];
        //订单总金额
        $toSubmit['payment_method'] = 'wechat';
        //payment_method
        $toSubmit['return_url'] = $root . "latipayrespond.php";
        //return_url
        $toSubmit['callback_url'] = $root . "latipaycallback.php";
        //后台回调地址

        $sign = '';
        foreach ($toSubmit as $key => $value) {
            $sign .= $value;
        }

        $post_data =
            array(
                'signature' => hash_hmac('sha256', $sign, $ikey),
                'wallet_id' => $walletid,
                'amount' => $order_latipay['order_amount'],
                'user_id' => $merId,
                'merchant_reference' => date(Ymd) . '-' . $merId . '-' . ($order_latipay['order_sn']),
                'currency' => 'CNY',
                'return_url' => $root . "latipayrespond.php",
                'callback_url' => $root . "latipaycallback.php",
                'ip' => real_ip(),
                'version' => '2.0',
                'product_name' => $order_latipay['order_sn'],
                'payment_method' => 'wechat',
                'present_qr' => '1'
            );

        $arr = json_encode($post_data);

        $url = 'https://api.latipay.net/v2/transaction/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
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
        $api_key = $payment['latipaywechat_key'];

        $signature_string = $order_latipayId . $payment_method . $status . $currency . $amount;
        $signature = hash_hmac('sha256', $signature_string, $api_key);

        if ($signature == $_GET['signature']) {
            if ($status == "paid") {
                $order_latipay_sn = explode('-', $order_latipayId);
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
        $api_key = $payment['latipaywechat_key'];

        $signature_string = $order_latipayId . $payment_method . $status . $currency . $amount;
        $signature = hash_hmac('sha256', $signature_string, $api_key);

        if ($signature == $_POST['signature']) {
            if ($status == "paid") {
                $order_latipay_sn = explode('-', $order_latipayId);
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
