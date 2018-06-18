<?php
class app_pay_latipaywechat extends app{
    var $ver = 1.0;
    var $name='LatiPay_WeChat';
    var $website = 'http://www.b8st.com';
    var $author = 'b8stcom';
    var $help = '';
    var $type = 'latipaywechat';
    function install(){
        parent::install();
        return true;
    }

    function uninstall(){
    	$this->db->exec('delete from sdb_payment_cfg where pay_type ="'.$this->type.'"');
        return parent::uninstall();
    }

	function ctl_mapper(){
		return array(

        );
	}

}
?>