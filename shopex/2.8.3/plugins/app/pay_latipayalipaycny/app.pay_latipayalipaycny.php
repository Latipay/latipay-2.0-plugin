<?php

class app_pay_latipayalipaycny extends app
{
    var $ver = 2.0;
    var $name = 'LatiPay_Alipay';
    var $website = 'https://www.latipay.net/';
    var $author = 'latipay support';
    var $help = '';
    var $type = 'latipayalipaycny';

    function install()
    {
        parent::install();
        return true;
    }

    function uninstall()
    {
        $this->db->exec('delete from sdb_payment_cfg where pay_type ="' . $this->type . '"');
        return parent::uninstall();
    }

    function ctl_mapper()
    {
        return array();
    }

}

?>