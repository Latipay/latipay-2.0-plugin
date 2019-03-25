<?php
require('paymentPlugin.php');

class pay_latipayonlinebank extends paymentPlugin
{
    function pay_latipayonlinebank_callback($in, &$paymentId, &$money, &$message, &$tradeno)
    {
        $json = file_get_contents('php://input');
        error_log(date('Y-m-d H:i:s', time()) . "\tcallback.server.json=" . $json . "\n", 3, HOME_DIR . "/logs/latipayonlinebank-" . date('Y-m-d', time()) . ".log");
        error_log(date('Y-m-d H:i:s', time()) . "\tcallback.server.post=" . stripslashes(var_export(array_merge($_GET, $_POST), true)) . "\n", 3, HOME_DIR . "/logs/latipayonlinebank-" . date('Y-m-d', time()) . ".log");
        $paymentId = $in["merchant_reference"];
        $currency = $in["currency"];
        $status = $in["status"];
        $money = $amount = $in["amount"];
        $payment_method = $in["payment_method"];
        $tradeno = $transaction_id = $in["transaction_id"];
        if (!$tradeno) $tradeno = $paymentId;

        $user_id = $this->getConf($paymentId, 'user_id');
        $wallet_id = $this->getConf($paymentId, 'wallet_id');
        $api_key = $this->getConf($paymentId, 'api_key');

        $signature = $in["signature"];

        $mac = $paymentId . $payment_method . $status . $currency . $amount;
        $mysignature = hash_hmac('sha256', $mac, $api_key);

        if ($mysignature == $signature) {
            echo "sent";
            if ($status == "paid") {
                $message = "支付成功";
                return PAY_SUCCESS;
            } else {
                $message = '支付失败'; //"交易失败";
                return PAY_FAILED;
            }
        } else {
            echo "error";
            $message = "验证签名失败！";
            return PAY_ERROR;
        }
    }
}

?>
