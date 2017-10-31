<?php
/*
	Latipay V2 纽元通 支付接口
	
	插件交流 QQ群 50415210

	开发者: TT
	邮箱: 30171310@qq.com
	QQ:30171310
*/
class ModelPaymentLatipay2 extends Model {
	public function getMethod($address, $total) {
		$this->load->language('payment/latipay2');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('latipay2_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('latipay2_total') > 0 && $this->config->get('latipay2_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('latipay2_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		
		//此支付接口,支付货币为CNY，NZD或AUD
		$currencies = array(
			'CNY',
			'NZD',
			'AUD'
		);
		
		if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
			$status = false;
		}
		

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'latipay2',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('latipay2_sort_order')
			);
		}

		return $method_data;
	}
}