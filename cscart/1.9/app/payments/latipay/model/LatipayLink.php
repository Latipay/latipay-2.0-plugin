<?php

class LatipayPaymentLink
{

    private $href;

    private $rel;

    private $method;

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