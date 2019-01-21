<?php

namespace Magento5\Latipay\Controller\Standard;

class DebugInfo extends \Magento5\Latipay\Controller\LatipayAbstract
{
    protected $version = '2.0.1';

    public function execute()
    {
        $paymentMethod = $this->getPaymentMethod();
        $isDebug = $paymentMethod->getDebugInfo();

        if ($isDebug && $isDebug == 1) {
            echo '<br><br>';
            echo 'Latipay version : ' . $this->version . PHP_EOL;
            echo '<br><br>';

            echo phpinfo();
        } else {
            die('access denied');
        }

        die();
    }

}
