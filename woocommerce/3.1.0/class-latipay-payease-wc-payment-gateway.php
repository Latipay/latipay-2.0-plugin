<?php
if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly
class XHLatipayPayeaseForWC extends Abstract_XH_LATIPAY_Payment_Gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->icon = XH_LATIPAY_URL . '/images/payease.png';
        $this->method_title = __('Latipay - Online bank', XH_LATIPAY);
    }

    public function get_payment_method()
    {
        return 'onlineBank';
    }
}

?>
