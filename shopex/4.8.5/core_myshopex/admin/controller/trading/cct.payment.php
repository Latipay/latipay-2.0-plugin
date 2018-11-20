<?php
include_once CORE_DIR.'/admin/controller/trading/ctl.payment.php';
class cct_payment extends ctl_payment {
	function index(){
        $appmgr = &$this->system->loadModel('system/appmgr');
        $client = &$this->system->loadModel('service/apiclient');

        $client->key = SDS_API_KEY;
        $client->url = SDS_API;

        //$payment = $client->native_svc("payment.get_all_payments");

        if($payment['result'] == 'succ'){
            $allApp = $appmgr->getPaydata($payment['result_msg']);
            file_put_contents(HOME_DIR.'/sendtmp/allApp.log',serialize($allApp));
        }

        $allApp = file_exists(HOME_DIR.'/sendtmp/allApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/allApp.log')
                : file_get_contents(HOME_DIR.'/sendtmp/defaultApp.log');
        $allApp = unserialize($allApp);
        if ( !$allApp ) unset($allApp);

        $useApp = file_exists(HOME_DIR.'/sendtmp/useApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/useApp.log') : '';
        $useApp = unserialize($useApp);
        if ( !$useApp ) unset($useApp);
        $allApp[] = $this->getcard($allApp);
        $this->getcarduse($useApp);

        $this->pagedata['allNum'] = count($allApp);
        $this->pagedata['useNum'] = count($useApp);
        $this->pagedata['allPay'] = $allApp;
        $this->pagedata['usePay'] = $useApp;
        $this->page('payment/pay_index.html');
    }

    function getcard(&$allApp){
		$allApp[] = array(
            'pay_id' => '99',
            'pay_name' => 'LatiPay_Alipay',
            'pay_ident' => 'pay_latipayalipay',
            'version' => '1.0',
            'pay_intro' => 'LatiPay_Alipay',
            'pay_contents' => 'LatiPay_Alipay',
            'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayalipay.tar',
            'pay_type' => 'common',
            'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayalipay.gif',
            'sort' => '10',
            'hidden' => false,
            'status' => true,
            'set' => true,
            'disable' => false,
            'count' => 1,
        );
		$allApp[] = array(
            'pay_id' => '99',
            'pay_name' => 'LatiPay_WeChat',
            'pay_ident' => 'pay_latipaywechat',
            'version' => '1.0',
            'pay_intro' => 'LatiPay_WeChat',
            'pay_contents' => 'LatiPay_WeChat',
            'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipaywechat.tar',
            'pay_type' => 'common',
            'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipaywechat.gif',
            'sort' => '10',
            'hidden' => false,
            'status' => true,
            'set' => true,
            'disable' => false,
            'count' => 1,
        );

		$allApp[] = array(
            'pay_id' => '99',
            'pay_name' => 'LatiPay_OnlineBank',
            'pay_ident' => 'pay_latipayonlinebank',
            'version' => '1.0',
            'pay_intro' => 'LatiPay_OnlineBank',
            'pay_contents' => 'LatiPay_OnlineBank',
            'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayonlinebank.tar',
            'pay_type' => 'common',
            'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayonlinebank.gif',
            'sort' => '10',
            'hidden' => false,
            'status' => true,
            'set' => true,
            'disable' => false,
            'count' => 1,
        );

		$allApp[] = array(
            'pay_id' => '99',
            'pay_name' => 'LatiPay_Alipay_CNY',
            'pay_ident' => 'pay_latipayalipaycny',
            'version' => '1.0',
            'pay_intro' => 'LatiPay_Alipay_CNY',
            'pay_contents' => 'LatiPay_Alipay_CNY',
            'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayalipaycny.tar',
            'pay_type' => 'common',
            'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayalipaycny.gif',
            'sort' => '10',
            'hidden' => false,
            'status' => true,
            'set' => true,
            'disable' => false,
            'count' => 1,
        );
		$allApp[] = array(
            'pay_id' => '99',
            'pay_name' => 'LatiPay_WeChat_CNY',
            'pay_ident' => 'pay_latipaywechatcny',
            'version' => '1.0',
            'pay_intro' => 'LatiPay_WeChat_CNY',
            'pay_contents' => 'LatiPay_WeChat_CNY',
            'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipaywechatcny.tar',
            'pay_type' => 'common',
            'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipaywechatcny.gif',
            'sort' => '10',
            'hidden' => false,
            'status' => true,
            'set' => true,
            'disable' => false,
            'count' => 1,
        );
        return $allApp;
    }

    function getcarduse(&$useApp){
        $oPay = &$this->system->loadModel('trading/payment');
		$row = $oPay->db->selectrow("select * from sdb_payment_cfg where pay_type='latipayalipay'");
        if($row){
            $useApp[] = array(
                'pay_id' => '99',
                'pay_name' => 'LatiPay_Alipay',
                'pay_ident' => 'pay_latipayalipay',
                'version' => '1.0',
                'pay_intro' => 'LatiPay_Alipay',
                'pay_contents' => 'LatiPay_Alipay',
                'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayalipay.tar',
                'pay_type' => 'common',
                'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayalipay.gif',
                'sort' => '10',
                'hidden' => false,
                'status' => true,
                'disabled' => $row['disabled'],
                'count' => 1,
                'id' => $row['id'],
                'custom_name' => $row['custom_name'],
                'dis' => $row['disabled'],
            );
        }

		$row = $oPay->db->selectrow("select * from sdb_payment_cfg where pay_type='latipaywechat'");
        if($row){
            $useApp[] = array(
                'pay_id' => '99',
                'pay_name' => 'LatiPay_WeChat',
                'pay_ident' => 'pay_latipaywechat',
                'version' => '1.0',
                'pay_intro' => 'LatiPay_WeChat',
                'pay_contents' => 'LatiPay_WeChat',
                'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipaywechat.tar',
                'pay_type' => 'common',
                'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipaywechat.gif',
                'sort' => '10',
                'hidden' => false,
                'status' => true,
                'disabled' => $row['disabled'],
                'count' => 1,
                'id' => $row['id'],
                'custom_name' => $row['custom_name'],
                'dis' => $row['disabled'],
            );
        }

		$row = $oPay->db->selectrow("select * from sdb_payment_cfg where pay_type='latipayonlinebank'");
        if($row){
            $useApp[] = array(
                'pay_id' => '99',
                'pay_name' => 'LatiPay_OnlineBank',
                'pay_ident' => 'pay_latipayonlinebank',
                'version' => '1.0',
                'pay_intro' => 'LatiPay_OnlineBank',
                'pay_contents' => 'LatiPay_OnlineBank',
                'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayonlinebank.tar',
                'pay_type' => 'common',
                'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayonlinebank.gif',
                'sort' => '10',
                'hidden' => false,
                'status' => true,
                'disabled' => $row['disabled'],
                'count' => 1,
                'id' => $row['id'],
                'custom_name' => $row['custom_name'],
                'dis' => $row['disabled'],
            );
        }

		$row = $oPay->db->selectrow("select * from sdb_payment_cfg where pay_type='latipayalipaycny'");
        if($row){
            $useApp[] = array(
                'pay_id' => '99',
                'pay_name' => 'LatiPay_Alipay_CNY',
                'pay_ident' => 'pay_latipayalipaycny',
                'version' => '1.0',
                'pay_intro' => 'LatiPay_Alipay_CNY',
                'pay_contents' => 'LatiPay_Alipay_CNY',
                'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipayalipaycny.tar',
                'pay_type' => 'common',
                'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipayalipaycny.gif',
                'sort' => '10',
                'hidden' => false,
                'status' => true,
                'disabled' => $row['disabled'],
                'count' => 1,
                'id' => $row['id'],
                'custom_name' => $row['custom_name'],
                'dis' => $row['disabled'],
            );
        }

		$row = $oPay->db->selectrow("select * from sdb_payment_cfg where pay_type='latipaywechatcny'");
        if($row){
            $useApp[] = array(
                'pay_id' => '99',
                'pay_name' => 'LatiPay_WeChat_CNY',
                'pay_ident' => 'pay_latipaywechatcny',
                'version' => '1.0',
                'pay_intro' => 'LatiPay_WeChat_CNY',
                'pay_contents' => 'LatiPay_WeChat_CNY',
                'pay_down_url' => 'http://sds.ecos.shopex.cn/payments/apps/pay_latipaywechatcny.tar',
                'pay_type' => 'common',
                'pay_logo' => 'http://sds.ecos.shopex.cn/payments/logos/pay_latipaywechatcny.gif',
                'sort' => '10',
                'hidden' => false,
                'status' => true,
                'disabled' => $row['disabled'],
                'count' => 1,
                'id' => $row['id'],
                'custom_name' => $row['custom_name'],
                'dis' => $row['disabled'],
            );
        }
    }

    function install_app($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $refesh = &$this->system->loadModel('system/addons');
        $payment = $this->system->loadModel('trading/payment');
        if($appmgr->install($ident,false)){
            $allApp = file_exists(HOME_DIR.'/sendtmp/allApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/allApp.log')
                : file_get_contents(HOME_DIR.'/sendtmp/defaultApp.log');
            $allApp = unserialize($allApp);

            $useApp = file_exists(HOME_DIR.'/sendtmp/useApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/useApp.log')
                : '';
            $useApp = unserialize($useApp);

            foreach( $useApp as $v ) {
                $useApp_[$v['pay_ident']] = $v['pay_ident'];
            }

            foreach($allApp as $key=>$val){
                if( !isset($useApp_[$ident]) && $val['pay_ident'] == $ident){
                    $val['disabled'] = 'true';
                    $useApp[] = $val;
                }
            }

            file_put_contents(HOME_DIR.'/sendtmp/useApp.log',serialize($useApp));

            if(!$_SESSION['updatePayment']){
                $plugin = $appmgr->getAppName($ident);
                $data['custom_name'] = $plugin['plugin_name'];
                $data['pay_type'] = substr($ident,4);
                $data['disabled'] = 'true';
                
                $payment->insertPaymentApp($data, $err);
            }

            unset($_SESSION['updatePayment']);

            $this->clear_all_cache();

            echo'<script>W.page(\'index.php?ctl=trading/payment&act=index\',{onComplete:function(){$(\'main\').setStyle(\'width\',window.mainwidth);}})</script>';
        }else{
            $this->end(false,'安装失败');
        }
    }

    function deletePayment($ident){        
        $this->begin('index.php?ctl=trading/payment&act=index');
        // 中心登记
        $this->sendRequestAsync($ident,'delete');
        // 本地删除
        $this->deletePaymentDb($ident);        
        // 删除目录
        //!is_dir(PLUGIN_DIR."/app/".$ident) || deleteDir(PLUGIN_DIR."/app/".$ident);
        $this->clear_all_cache();
        $this->end(true,'操作成功');
    }

    function deletePaymentDb($ident){
        $oPayment = $this->system->loadModel('trading/payment');
        if($c = unserialize( file_get_contents( HOME_DIR."/sendtmp/useApp.log"))){
            foreach ( $c as $k => $v )
            {
                            if ( $ident == $v['pay_ident'] )
                            {
                                            unset( $c[$k] );
                            }
            }
            file_put_contents( HOME_DIR."/sendtmp/useApp.log", serialize( $c ) );
        }
        $oPayment->db->exec( "UPDATE sdb_payment_cfg SET disabled=\"true\" WHERE pay_type =".$this->db->quote( substr( $ident, 4 ) ) );
        //$oPayment->db->exec( "DELETE FROM sdb_plugins WHERE plugin_package=".$this->db->quote( $ident ) );
        return TRUE;
    }
}

?>
