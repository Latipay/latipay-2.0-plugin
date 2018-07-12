<?php
class ControllerExtensionPaymentLatipay2 extends Controller {
	private $error = array();

	public function index() {
		
		
		//给数据添加字段,用于邮件发送使用
		$sql = $this->db->query("Describe `" . DB_PREFIX . "order` `if_email_latipay2`");
		if($sql->num_rows){
			//echo "you";	
		}else{
			//echo 'no';	
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `if_email_latipay2` TINYINT NOT NULL ");
		}
		
		
		$this->load->language('extension/payment/latipay2');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_latipay2', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');

		$data['entry_user_id'] = $this->language->get('entry_user_id');
		
		$data['entry_wallet_id_nzd'] = $this->language->get('entry_wallet_id_nzd');
		$data['entry_wallet_id_aud'] = $this->language->get('entry_wallet_id_aud');
		$data['entry_wallet_id_cny'] = $this->language->get('entry_wallet_id_cny');

		$data['entry_huan_jing'] = $this->language->get('entry_huan_jing');
		$data['entry_huan_jing_ce_shi'] = $this->language->get('entry_huan_jing_ce_shi');
		$data['entry_huan_jing_zheng_shi'] = $this->language->get('entry_huan_jing_zheng_shi');
		
		$data['entry_payment_method_url'] = $this->language->get('entry_payment_method_url');
		$data['entry_gateway_url'] = $this->language->get('entry_gateway_url');
		
		
		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['user_id'])) {
			$data['error_user_id'] = $this->error['user_id'];
		} else {
			$data['error_user_id'] = '';
		}

		if (isset($this->error['wallet_id_nzd'])) {
			$data['error_wallet_id_nzd'] = $this->error['wallet_id_nzd'];
		} else {
			$data['error_wallet_id_nzd'] = '';
		}
		
		
		if (isset($this->error['wallet_id_aud'])) {
			$data['error_wallet_id_aud'] = $this->error['wallet_id_aud'];
		} else {
			$data['error_wallet_id_aud'] = '';
		}
		
		
		if (isset($this->error['wallet_id_cny'])) {
			$data['error_wallet_id_cny'] = $this->error['wallet_id_cny'];
		} else {
			$data['error_wallet_id_cny'] = '';
		}

		if (isset($this->error['api_key'])) {
			$data['error_api_key'] = $this->error['api_key'];
		} else {
			$data['error_api_key'] = '';
		}
		
		if (isset($this->error['payment_method_url'])) {
			$data['error_payment_method_url'] = $this->error['payment_method_url'];
		} else {
			$data['error_payment_method_url'] = '';
		}
		
		if (isset($this->error['gateway_url'])) {
			$data['error_gateway_url'] = $this->error['gateway_url'];
		} else {
			$data['error_gateway_url'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/latipay2', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/latipay2', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_latipay2_user_id'])) {
			$data['payment_latipay2_user_id'] = $this->request->post['payment_latipay2_user_id'];
		} else {
			$data['payment_latipay2_user_id'] = $this->config->get('payment_latipay2_user_id');
		}

		if (isset($this->request->post['payment_latipay2_wallet_id_nzd'])) {
			$data['payment_latipay2_wallet_id_nzd'] = $this->request->post['payment_latipay2_wallet_id_nzd'];
		} else {
			$data['payment_latipay2_wallet_id_nzd'] = $this->config->get('payment_latipay2_wallet_id_nzd');
		}
		
		
		if (isset($this->request->post['payment_latipay2_wallet_id_aud'])) {
			$data['payment_latipay2_wallet_id_aud'] = $this->request->post['payment_latipay2_wallet_id_aud'];
		} else {
			$data['payment_latipay2_wallet_id_aud'] = $this->config->get('payment_latipay2_wallet_id_aud');
		}
		
		if (isset($this->request->post['payment_latipay2_wallet_id_cny'])) {
			$data['payment_latipay2_wallet_id_cny'] = $this->request->post['payment_latipay2_wallet_id_cny'];
		} else {
			$data['payment_latipay2_wallet_id_cny'] = $this->config->get('payment_latipay2_wallet_id_cny');
		}

		if (isset($this->request->post['payment_latipay2_api_key'])) {
			$data['payment_latipay2_api_key'] = $this->request->post['payment_latipay2_api_key'];
		} else {
			$data['payment_latipay2_api_key'] = $this->config->get('payment_latipay2_api_key');
		}
		
		
		if (isset($this->request->post['payment_latipay2_huan_jing'])) {
			$data['payment_latipay2_huan_jing'] = $this->request->post['payment_latipay2_huan_jing'];
		} else {
			$data['payment_latipay2_huan_jing'] = $this->config->get('payment_latipay2_huan_jing');
		}
		
		
		if (isset($this->request->post['payment_latipay2_payment_method_url'])) {
			$data['payment_latipay2_payment_method_url'] = $this->request->post['payment_latipay2_payment_method_url'];
		} else {
			$data['payment_latipay2_payment_method_url'] = $this->config->get('payment_latipay2_payment_method_url');
		}
		
		
		if (isset($this->request->post['payment_latipay2_gateway_url'])) {
			$data['payment_latipay2_gateway_url'] = $this->request->post['payment_latipay2_gateway_url'];
		} else {
			$data['payment_latipay2_gateway_url'] = $this->config->get('payment_latipay2_gateway_url');
		}


		if (isset($this->request->post['payment_latipay2_total'])) {
			$data['payment_latipay2_total'] = $this->request->post['payment_latipay2_total'];
		} else {
			$data['payment_latipay2_total'] = $this->config->get('payment_latipay2_total');
		}

		if (isset($this->request->post['payment_latipay2_order_status_id'])) {
			$data['payment_latipay2_order_status_id'] = $this->request->post['payment_latipay2_order_status_id'];
		} else {
			$data['payment_latipay2_order_status_id'] = $this->config->get('payment_latipay2_order_status_id');
		}

        $this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_latipay2_geo_zone_id'])) {
			$data['payment_latipay2_geo_zone_id'] = $this->request->post['payment_latipay2_geo_zone_id'];
		} else {
			$data['payment_latipay2_geo_zone_id'] = $this->config->get('payment_latipay2_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_latipay2_status'])) {
			$data['payment_latipay2_status'] = $this->request->post['payment_latipay2_status'];
		} else {
			$data['payment_latipay2_status'] = $this->config->get('payment_latipay2_status');
		}

		if (isset($this->request->post['payment_latipay2_sort_order'])) {
			$data['payment_latipay2_sort_order'] = $this->request->post['payment_latipay2_sort_order'];
		} else {
			$data['payment_latipay2_sort_order'] = $this->config->get('payment_latipay2_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/latipay2', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/latipay2')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_latipay2_user_id']) {
			$this->error['user_id'] = $this->language->get('error_user_id');
		}

		if (!$this->request->post['payment_latipay2_wallet_id_nzd']) {
			//$this->error['wallet_id_nzd'] = $this->language->get('error_wallet_id_nzd');
		}
		
		if (!$this->request->post['payment_latipay2_wallet_id_aud']) {
			//$this->error['wallet_id_aud'] = $this->language->get('error_wallet_id_aud');
		}
		
		if (!$this->request->post['payment_latipay2_wallet_id_cny']) {
			//$this->error['wallet_id_cny'] = $this->language->get('error_wallet_id_cny');
		}
		
		if (!$this->request->post['payment_latipay2_api_key']) {
			$this->error['api_key'] = $this->language->get('error_api_key');
		}
		
		
		if (!$this->request->post['payment_latipay2_payment_method_url']) {
			$this->error['payment_method_url'] = $this->language->get('error_payment_method_url');
		}
		
		if (!$this->request->post['payment_latipay2_gateway_url']) {
			$this->error['gateway_url'] = $this->language->get('error_gateway_url');
		}

		return !$this->error;
	}
}
