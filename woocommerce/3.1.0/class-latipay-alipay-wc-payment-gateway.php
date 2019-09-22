<?php
if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly
class XHLatipayAlipayForWC extends Abstract_XH_LATIPAY_Payment_Gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->icon = XH_LATIPAY_URL . '/images/alipay.png';
        $this->method_title = __('Latipay - Alipay', XH_LATIPAY);
    }

    public function get_payment_method()
    {
        return 'Alipay';
    }
}

?>
