<?php

class LatipayPaymentSource
{

    private $first_name;

    private $middle_name;

    private $last_name;

    private $address1;

    private $address2;

    private $country;

    private $city;

    private $state;

    private $zip;

    private $phone;

    private $email;

    private $card_number;

    private $expiration_month;

    private $expiration_year;

    private $cv_number;

    private $client_ip;

    private $cpf;

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