<?php
require('paymentPlugin.php');
class pay_latipaywechat extends paymentPlugin{
    var $name = 'LatiPay_WeChat';
    var $logo = 'allinpay';
    var $version = 20111202;
    var $charset = 'utf-8';
    //var $submitUrl = 'https://ibsbjstar.allinpay.com.cn/app/allinpayMain'; //
	var $submitUrl = 'https://merchant.latipay.co.nz/api/show.action';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("NZD"=>"0");
    var $supportArea = array('AREA_NZD');
    var $desc = 'LatiPay_WeChat';
    var $orderby = 100;//不能和其他支付方式的orderby重复，否则重复的会显示不出来
    var $head_charset="utf-8";

    function toSubmit($payment){
        $user_id = $this->getConf($payment["M_OrderId"], 'user_id');
        $wallet_id = $this->getConf($payment["M_OrderId"], 'wallet_id');
		$api_key = $this->getConf($payment["M_OrderId"], 'api_key');

		$subject = "Pay:".$payment['M_OrderNO'];
        $subject = str_replace("'",'`',trim($subject));
        $subject = str_replace('"','`',$subject);

		$params = array();
		$params["wallet_id"] = $wallet_id;
		$params["amount"] = $payment['M_Amount'];
		$params["user_id"] = $user_id;
		$params["merchant_reference"] = $payment["M_OrderId"];
		$params["currency"] = "NZD";
		$params["return_url"] = $this->callbackUrl;
		$params["callback_url"] = $this->serverCallbackUrl;
		$params["ip"] = remote_addr();
		$params["version"] = "2.0";
		$params["product_name"] = $subject;
		$params["payment_method"] = "wechat";//alipay,wechat,onlineBank
		$params["present_qr"] = "1";

		$mac = $params["user_id"].$params["wallet_id"].$params["amount"].$params["payment_method"].$params["return_url"].$params["callback_url"];
		$signature = hash_hmac('sha256', $mac, $api_key);
		$params["signature"] = $signature;

		$response = $this->curl("https://api.latipay.net/v2/transaction", json_encode($params));

		//print_r($response);exit;

		$resp = json_decode($response, true);
		if($resp['code'] == "0"){
			$nonce = $resp['nonce'];
			$host_url = $resp['host_url'];
			$signature = $resp['signature'];

			$url = $host_url."/".$nonce;
			//进入支付
			header( "Location: $url");exit;
		}else{
			echo $resp['message'];exit;
		}
    }

	function curl($url, $data){ // 模拟提交数据函数
		$strData = is_string($data) ? $data : http_build_query($data);

		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		}
		curl_setopt($curl, CURLOPT_USERAGENT, "SDK V1.0"); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0); // 使用自动跳转
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, $strData); // Post提交的数据包
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		  "Content-Type: application/json"
		));

		$response = curl_exec($curl); // 执行操作
		if (curl_errno($curl)) {
		   return false;
		}
		curl_close($curl); // 关闭CURL会话
		return $response; // 返回数据
	}
	
    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
		$json = file_get_contents('php://input');
		error_log(date('Y-m-d H:i:s',time())."\tcallback.json=".$json."\n",3,HOME_DIR."/logs/latipaywechat-".date('Y-m-d',time()).".log");
		error_log(date('Y-m-d H:i:s',time())."\tcallback.post=".stripslashes(var_export(array_merge($_GET,$_POST),true))."\n",3,HOME_DIR."/logs/latipaywechat-".date('Y-m-d',time()).".log");
		$paymentId = $in["merchant_reference"];
		$currency = $in["currency"];
		$status = $in["status"];
		$money = $amount = $in["amount"];
		$payment_method = $in["payment_method"];
		$tradeno = $transaction_id = $in["transaction_id"];
		if(!$tradeno) $tradeno = $paymentId;
		
		$user_id = $this->getConf($paymentId, 'user_id');
        $wallet_id = $this->getConf($paymentId, 'wallet_id');
		$api_key = $this->getConf($paymentId, 'api_key');

		$signature = $in["signature"];

		$mac = $paymentId.$payment_method.$status.$currency.$amount;
		$mysignature = hash_hmac('sha256', $mac, $api_key);

		if ($mysignature == $signature) {
			if($status == "paid"){
				$message = "支付成功";
				return PAY_SUCCESS;
			}else{
				$message = '支付失败'; //"交易失败";
                return PAY_FAILED;
			}
		}else{
			$message = "验证签名失败！";
            return PAY_ERROR;
		}
    }
    
    function getfields(){
        return array(
            'user_id'=>array(
                    'label'=>'User ID',
                    'type'=>'string'
            ),
            'wallet_id'=>array(
                    'label'=>'Wallet ID',
                    'type'=>'string'
            ),
			'api_key'=>array(
                    'label'=>'API Key',
                    'type'=>'string'
            ),
        );
    }
}
?>
