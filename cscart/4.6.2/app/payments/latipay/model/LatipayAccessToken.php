<?php

require_once('../lib/RestClient.php');

class LatipayAccessToken
{

    private $access_token;

    private $token_type;

    private $expires_in;

    private $scope;

    public static function create()
    {
        $client = new RestClient();
        $response = $client->url(LATIPAY_OPENAPI_URL . "/v1/oauth/token")
            ->username(LATIPAY_OPENAPI_USERNAME)
            ->password(LATIPAY_OPENAPI_PASSWORD)
            ->post(array(
                "grant_type" => "client_credentials",
                "scope" => "write,read"
            ));
        $array = json_decode($response, true);
        $token = new LatipayAccessToken($array);
        return $token;
    }

    public function __construct($array = array())
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
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