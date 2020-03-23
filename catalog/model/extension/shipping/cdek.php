<?php
class ModelExtensionShippingCdek extends Model {
	private $receiver_city_zip;

	function getQuote($address) {
		$this->load->language('extension/shipping/cdek');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_cdek_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_cdek_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$cdek_tax = (int)$this->config->get('shipping_cdek_tax');
		$cdek_self_tax = (int)$this->config->get('shipping_cdek_self_tax');

		$this->receiver_city_zip = $address['postcode'];


		$method_data = array();

		if ($status) {
			$quote_data = array();

			if ($json = $this->getPriceByApi($cdek_tax)) {
				$quote_data['cdek'] = array(
					'code'         => 'cdek.cdek',
					'title'        => $this->language->get('text_description'),
					'cost'         => $json->price,
					'tax_class_id' => $this->config->get('shipping_cdek_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($json->price, $this->config->get('shipping_cdek_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']) . " (" . $json->deliveryPeriodMin . "-" . $json->deliveryPeriodMax . " дн.)"
				);
			}

			if ($json = $this->getPriceByApi($cdek_self_tax)) {
				$quote_data['cdek_self'] = array(
					'code'         => 'cdek.cdek_self',
					'title'        => $this->language->get('text_self_description'),
					'cost'         => $json->price,
					'tax_class_id' => $this->config->get('shipping_cdek_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($json->price, $this->config->get('shipping_cdek_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']) . " (" . $json->deliveryPeriodMin . "-" . $json->deliveryPeriodMax . " дн.)"
				);
			}

			if (count($quote_data)) {
				$method_data = array(
					'code'       => 'cdek',
					'title'      => $this->language->get('text_title'),
					'quote'      => $quote_data,
					'sort_order' => $this->config->get('shipping_cdek_sort_order'),
					'error'      => false
				);
			}
		}

		return $method_data;
	}

	function getPriceByApi($tax = '10')
	{
		$api_url = "http://api.cdek.ru/calculator/calculate_price_by_json.php";
		$sender_city_zip = $this->config->get('shipping_cdek_zip');

		$goods = array();
		foreach ($this->cart->getProducts() as $product) {
			if ($product['shipping']) {
				$weight = $product["weight"] ? $product["weight"] : 50;

				for ($i=0; $i < $product['quantity']; $i++) { 
					$goods[] = array(
						'weight'  => $weight/1000,
						'volume'	=> 0.001,
					);
				}
			}
		}
		$cdek_data = array(
			'version' => "1.0",
			'senderCityPostCode' => $sender_city_zip,
			'receiverCityPostCode' => $this->receiver_city_zip,
			'tariffId' => $tax,
			'goods' => $goods
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($cdek_data),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			),
		));

		$json = json_decode(curl_exec($curl));
		curl_close($curl);

		if (isset($json->result->price)) {
			return $json->result;
		}

		return false;
	}
}