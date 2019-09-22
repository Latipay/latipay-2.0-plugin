<?php
if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly
class XHLatipayMoneymoreForWC extends Abstract_XH_LATIPAY_Payment_Gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->icon = XH_LATIPAY_URL . '/images/moneymore.png';
        $this->method_title = __('Latipay - MoneyMore', XH_LATIPAY);
    }

    public function get_payment_method()
    {
        return 'MoneyMore';
    }
}

?>
