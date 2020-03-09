<?php

namespace Latipay;

class Demo
{
    private $api_key;
    private $user_id;
    private $wallet_id;

    public function __construct($config)
    {
        $this->api_key = isset($config['api_key']) ? $config['api_key'] : '';
        $this->user_id = isset($config['user_id']) ? $config['user_id'] : '';
        $this->wallet_id = isset($config['wallet_id']) ? $config['wallet_id'] : '';

    }

    /**
     * 下单,获取支付页面地址
     * @param $order
     * @return bool|mixed
     */
    public function payOrder($order = [])
    {
        $order['user_id'] = $this->user_id;
        $order['wallet_id'] = $this->wallet_id;

        $result = Core::doRequest($order, $this->api_key);


        /** result数据，redirect_url为支付url
         * Array(
         * [status] => success
         * [redirect_url] => https://pay.latipay.net/pay/7d5a88119354301ad3fc250404493bd27abf4467283a061d1ed11860a46e1bf3
         * )
         */

        return $result;
    }

    /**
     * 支付完成后（成功或失败）浏览器重定向
     * @param $data
     */
    public function returnBack()
    {
        //例子 GET 获取参数
        //https://www.merchant.com/latipay?merchant_reference=dsi39ej430sks03&payment_method=alipay&status=paid&currency=NZD&amount=100.00&signature=14d5b06a2a5a2ec509a148277ed4cbeb3c43301b239f080a3467ff0aba4070e3
        $data = [
            "merchant_reference" => "1567568358",
            "order_id" => "2019090400003370",
            "currency" => "NZD",
            "status" => "paid",
            "payment_method" => "wechat",
            "signature" => "103600c090f5f0738a2df054f4b",
            "createDate" => "2019-09-04 03:39:19",
            "amount" => "0.02",
        ];

        $result = Core::verify($data, $this->api_key); //验签结果

        //重定向逻辑
    }

    /**
     * 支付结果异步通知
     * @return mixed
     */
    public function notify()
    {
        //例子
        //POST 商户端的 callback_url
        //Content-Type: application/x-www-form-urlencoded
        $data = [
            "merchant_reference" => "1567568358",
            "order_id" => "2019090400003370",
            "currency" => "NZD",
            "status" => "paid",
            "payment_method" => "wechat",
            "signature" => "103600c090f5f0738a2df054f4b",
            "createDate" => "2019-09-04 03:39:19",
            "amount" => "0.02",
        ];

        try {
            $result = Core::verify($data, $this->api_key); //验签结果
            //data内容同上

            //回调业务逻辑

            //异步通知成功：Latipay服务器期望收到 sent 文本
            die('sent');

        } catch (\Exception $e) {
            // $e->getMessage();
        }
    }


    /**
     * 查询订单
     * @param $orderId
     * @return mixed
     */
    public function queryOrder($orderId)
    {
        return Core::find($orderId, $this->user_id, $this->api_key);
    }

}
