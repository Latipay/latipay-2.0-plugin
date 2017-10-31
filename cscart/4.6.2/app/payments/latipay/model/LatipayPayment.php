<?php

require_once('../lib/RestClient.php');
require_once('../lib/IP.php');
require_once('LatipayLink.php');
require_once('LatipayPaymentSource.php');

class LatipayPayment
{

    private $payment_id;

    private $transaction_id;

    private $create_time;

    private $update_time;

    private $payment_method;

    private $status;

    private $source;

    private $return_url;

    private $cancel_url;

    private $notify_url;

    private $order_id;

    private $description;

    private $amount;

    private $currency;

    private $request_type;

    private $links;

    private $website;

    private $fee;

    public static function create($data)
    {
        $token = LatipayAccessToken::create();
        $data['source']['client_ip'] = IP::clientIP();
        $client = new RestClient();
        $response = $client->url(LATIPAY_OPENAPI_URL . "/v1/payment")
            ->accessToken($token->access_token)
            ->postJson($data);
        $array = json_decode($response, true);
        $payment = new LatipayPayment($array);
        return $payment;
    }

    public static function get($payment_id)
    {
        $token = LatipayAccessToken::create();
        $client = new RestClient();
        $response = $client->url(LATIPAY_OPENAPI_URL . "/v1/payment/" . $payment_id)
            ->accessToken($token->access_token)
            ->get();
        $array = json_decode($response, true);
        $payment = new LatipayPayment($array);
        return $payment;
    }

    public function __construct($array = array())
    {
        foreach ($array as $key => $value) {
            if ($key == 'source') {
                $this->source = new LatipayPaymentSource($value);
            } else {
                $this->$key = $value;
            }
        }
    }

    public function getLink($rel)
    {
        if ($this->links) {
            foreach ($this->links as $link) {
                if ($link['rel'] == $rel) {
                    return $link['href'];
                }
            }
        }
        return null;
    }

    public function __get($property_name)
    {
        if (isset($this->$property_name)) {
            return ($this->$property_name);
        } else {
            return (NULL);
        }
    }

    public function __set($property_name, $value)
    {
        $this->$property_name = $value;
    }

}