<?php

namespace Latipay;

class Core
{

    private static $transactionUrl = 'https://api.latipay.net/v2/transaction';

    public static function doRequest($order, $apiKey)
    {

        $orderData = self::getOrderData($order, $apiKey);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$transactionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            //var_dump($error);
            return false;
        }

        $payment = json_decode($response, true);

        $return = [];
        if (isset($payment['host_url']) && $payment['host_url'] != '') {
            $response_signature = hash_hmac('sha256', $payment['nonce'] . $payment['host_url'], $apiKey);
            if ($response_signature == $payment['signature']) {
                $redirect_url = $payment['host_url'] . '/' . $payment['nonce'];
                $return['status'] = 'success';
                $return['redirect_url'] = $redirect_url;
            }
        }

        return $return;
    }

    private static function getOrderData($payData, $apiKey)
    {

        $data = array(
            'user_id' => $payData['user_id'],
            'wallet_id' => $payData['wallet_id'],
            'amount' => sprintf("%.2f", round($payData['amount'], 2)),
            'payment_method' => strtolower($payData['payment_method']),
            'return_url' => $payData['return_url'],
            'callback_url' => $payData['callback_url'],
            'merchant_reference' => $payData['merchant_reference'],
            'ip' => isset($payData['ip']) ? $payData['ip'] : '127.0.0.1',
            'product_name' => $payData['product_name'],
            'version' => '2.0',
        );

        if ($data['payment_method'] == "wechat") {
            $data['present_qr'] = 1;
        }

        ksort($data);
        $item = array();
        foreach ($data as $key => $value) {
            $item[] = $key . "=" . $value;
        }
        $_prehash = join("&", $item);
        $signature = hash_hmac('sha256', $_prehash . $apiKey, $apiKey);
        $data['signature'] = $signature;

        return $data;
    }


    public static function verify($data, $apiKey)
    {
        $paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
        $currency = isset($data['currency']) ? $data['currency'] : '';
        $amount = isset($data['amount']) ? $data['amount'] : '';
        $orderId = isset($data['merchant_reference']) ? $data['merchant_reference'] : '';

        $signatureString = $orderId . $paymentMethod . $status . $currency . $amount;
        $signature = hash_hmac('sha256', $signatureString, $apiKey);
        if (isset($data['signature']) && $signature == $data['signature']) {
            return true;
        }

        return false;
    }

    public static function find($orderId, $userId, $apiKey)
    {
        $signatureString = $orderId . $userId;
        $signature = hash_hmac('sha256', $signatureString, $apiKey);

        $query = [
            'user_id' => $userId,
            'signature' => $signature,
        ];
        $url = self::$transactionUrl . '/' . $orderId . '?' . http_build_query($query);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            //var_dump($error);
            return false;
        }

        $transactionInfo = json_decode($response, true);

        $verifyResult = self::verify($transactionInfo, $apiKey);
        if ($verifyResult) {
            return $transactionInfo;
        }

        return false;
    }
}
