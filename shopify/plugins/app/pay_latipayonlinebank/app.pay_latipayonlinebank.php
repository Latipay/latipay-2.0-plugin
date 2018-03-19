<?php
class app_pay_latipayonlinebank extends app{
    var $ver = 1.0;
    var $name='LatiPay_OnlineBank';
    var $website = 'http://www.b8st.com';
    var $author = 'b8stcom';
    var $help = '';
    var $type = 'latipayonlinebank';
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