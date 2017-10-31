<?php

class RestClient
{

    private $timeout = 30;
    private $url;
    private $headers = array();
    private $post = false;
    private $data = array();

    public function execute()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, $this->post);

        if ($this->data) {
            if (!is_string($this->data)) {
                $this->data = http_build_query($this->data);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        }
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = curl_error($curl);
            throw new Exception($response);
        }
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode >= 300) {
            throw new Exception($response);
        }
        curl_close($curl);
        return $response;
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function headers($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function post($data = array())
    {
        $this->post = true;
        if ($data) {
            $this->data = $data;
        }
        return $this->execute();
    }

    public function postJson($data = array())
    {
        $this->post = true;
        $this->headers[] = "Content-Type: application/json";
        if ($data || is_array($data) || is_object($data)) {
            $this->data = json_encode($data);
        }
        return $this->execute();
    }

    public function get()
    {
        return $this->execute();
    }

}