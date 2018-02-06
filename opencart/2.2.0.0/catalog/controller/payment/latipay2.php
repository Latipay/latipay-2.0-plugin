<?php
/*
	Latipay V2 纽元通 支付接口
	
	插件交流 QQ群 50415210

	开发者: TT
	邮箱: 30171310@qq.com
	QQ:30171310
*/
include_once( DIR_SYSTEM . 'library/latipay2/lib/Latipay.php');
require_once( DIR_SYSTEM . 'library/latipay2/lib/IP.php');
class ControllerPaymentLatipay2 extends Controller {
	public function index()
    {
        $this->language->load('payment/latipay2');
        //Payment method:
        $data['text_payment_method'] = $this->language->get('text_payment_method');

        $data['text_alipay'] = $this->language->get('text_alipay');
        $data['text_wechat'] = $this->language->get('text_wechat');
        $data['text_onlineBank'] = $this->language->get('text_onlineBank');

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['text_loading'] = $this->language->get('text_loading');

        $data['continue'] = $this->url->link('checkout/success');
        $data['latipay_error'] = '';

        //根据 wallet_id + user_id 获取 get the curency and payment method
        if (isset($this->session->data['currency'])) {
            $currency = $this->session->data['currency'];
            if ($currency == 'NZD') {
                $wallet_id = trim($this->config->get('latipay2_wallet_id_nzd'));
            } else if ($currency == 'AUD') {
                $wallet_id = trim($this->config->get('latipay2_wallet_id_aud'));
            } else if ($currency == 'CNY') {
                $wallet_id = trim($this->config->get('latipay2_wallet_id_cny'));
            }
        }
        // 没有取到 wallet_id, 则不显示latipay
        if (!$wallet_id) {
            $data['latipay_error'] = "Latipay Error! Please try later.(Latipay wallet_id Not found. currency: {$this->session->data['currency']})";
            return $this->load->view('payment/latipay2', $data);
        }

        $user_id = trim($this->config->get('latipay2_user_id'));
        $api_key = trim($this->config->get('latipay2_api_key'));

        $_prehash = $wallet_id . $user_id;
        $signature = hash_hmac('sha256', $_prehash, $api_key);

        $url = trim($this->config->get('latipay2_payment_method_url')) . $wallet_id . "?user_id=" . $user_id . "&signature=" . $signature;

        //初始化
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        $error = curl_error($ch);
        //释放curl句柄
        curl_close($ch);

        if ($error) {
            $data['latipay_error'] = "Latipay Error! Please try later. (api request error:{$error})";
            return $this->load->view('payment/latipay2', $data);
        }

        $wallet = '';
        $apiData = json_decode($output, true);
        if (is_array($apiData) && isset($apiData['code']) && ($apiData['code'] === 0)) {
            $wallet = $apiData['payment_method'];
        }

        if (!$wallet) {
            $wallet = 'Wechat,Alipay';
        }

        $walletList = explode(',', $wallet);

        //打印获得的数据
        //print_r($html);
        //( [code] => 0 [message] => SUCCESS [currency] => CNY [payment_method] => Payease, Alipay, Wechat )

        $select_array = array();
        foreach ($walletList as $key => $value) {
            $select_array[] = array(
                'name' => $value,
                'value' => strtolower($value),
            );
        }

        $data['select_array'] = $select_array;

        return $this->load->view('payment/latipay2', $data);
    }
	
	public function confirm() {
		
		$json = array();
		
		$order_id = $this->session->data['order_id'];
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);		
		
		//payment_method
		$payment_method = $this->request->post['payment_method'];
		
		$gateway = trim($this->config->get('latipay2_gateway_url'));
		
		$url_return = HTTP_SERVER . "latipay2/url.php";//前台回调地址
		
		$url_notify = HTTP_SERVER . "latipay2/backUrl.php";//后台回调地址
		
		$api_key = trim($this->config->get('latipay2_api_key'));
		$user_id = trim($this->config->get('latipay2_user_id'));
		
		if(isset($this->session->data['currency'])){
			$currency = $this->session->data['currency'];
			
			if($currency == 'NZD'){
				$wallet_id = trim($this->config->get('latipay2_wallet_id_nzd'));
			}else if($currency == 'AUD'){
				$wallet_id = trim($this->config->get('latipay2_wallet_id_aud'));
			}else if($currency == 'CNY'){
				$wallet_id = trim($this->config->get('latipay2_wallet_id_cny'));
			}
			
		}else{
			$wallet_id = trim($this->config->get('latipay2_wallet_id_nzd'));	
		}

		$total = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);//订单总金额   
		
		$_prehash =  $user_id . $wallet_id . $total . $payment_method . $url_return . $url_notify;
		//FB::Log($_prehash);
		//FB::Log($api_key);
    	$signature = hash_hmac('sha256', $_prehash, $api_key);
		
		$latipay = new Latipay($gateway);
		$post_data = array(
			'wallet_id' => $wallet_id,
			'amount' => $total,
			'currency' => $order_info['currency_code'],
			'user_id' => $user_id,
			'merchant_reference' => $order_id,
			'return_url' => $url_return,
			'callback_url' => $url_notify,
			'ip' => IP::clientIP(),
			'version' => '2.0',
			'product_name' => 'Order #' . $order_id,
			'payment_method' => $payment_method, // wechat, alipay, onlineBank
			'present_qr' => '1', // wechat
			'signature' => $signature,
		);
		
		//print_r($post_data);exit;
	
		try {
	
			$payment =  $latipay->createPayment($post_data);
			//print_r($payment);
			if($payment['host_url'] != ''){
				$response_signature = hash_hmac('sha256', $payment['nonce'] . $payment['host_url'], $api_key);
				if ($response_signature == $payment['signature']) {
					$redirect_url = $payment['host_url'].'/'.$payment['nonce'];
					$json['success'] = 'ok';
					$json['redirect_url'] = $redirect_url;
					//Header("Location:$redirect_url");exit;
				}
			}else{
				$json['error'] = $payment['message'];
			}
			
			
	
		} catch (\Exception $e) {
	
			//echo 'CS Cart Error : ' . $e->getMessage();
			$json['error'] = 'error';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	//发送邮件
	public function callback() {
		
		$this->load->model('checkout/order_latipay2');
		$order_id = $this->request->post['order_id'];
		$order_status_id = $this->request->post['order_status_id'];

		//查看是否发送过数据
		$sql = $this->db->query("SELECT if_email_latipay2 FROM " . DB_PREFIX . "order WHERE order_id = '".$order_id."' LIMIT 1 ");
		if($sql->num_rows){
			if($sql->row['if_email_latipay2'] == '0'){
				
				$this->db->query("UPDATE " . DB_PREFIX . "order SET if_email_latipay2 = '1' WHERE order_id = '".$order_id."' ");
				//发送邮件
				$this->model_checkout_order_latipay2->addOrderHistory($order_id, $order_status_id , '' , 1);
				
			}else{
				//nothing
			}
		}
		
	}
}
