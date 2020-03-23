<?php
class ControllerExtensionShippingCdek extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/cdek');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_cdek', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/cdek', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/cdek', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		if (isset($this->request->post['shipping_cdek_zip'])) {
			$data['shipping_cdek_zip'] = $this->request->post['shipping_cdek_zip'];
		} else {
			$data['shipping_cdek_zip'] = $this->config->get('shipping_cdek_zip');
		}
		if (isset($this->request->post['shipping_cdek_tax'])) {
			$data['shipping_cdek_tax'] = $this->request->post['shipping_cdek_tax'];
		} else {
			$data['shipping_cdek_tax'] = $this->config->get('shipping_cdek_tax');
		}
		if (isset($this->request->post['shipping_cdek_self_tax'])) {
			$data['shipping_cdek_self_tax'] = $this->request->post['shipping_cdek_self_tax'];
		} else {
			$data['shipping_cdek_self_tax'] = $this->config->get('shipping_cdek_self_tax');
		}

		if (isset($this->request->post['shipping_cdek_tax_class_id'])) {
			$data['shipping_cdek_tax_class_id'] = $this->request->post['shipping_cdek_tax_class_id'];
		} else {
			$data['shipping_cdek_tax_class_id'] = $this->config->get('shipping_cdek_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['shipping_cdek_geo_zone_id'])) {
			$data['shipping_cdek_geo_zone_id'] = $this->request->post['shipping_cdek_geo_zone_id'];
		} else {
			$data['shipping_cdek_geo_zone_id'] = $this->config->get('shipping_cdek_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_cdek_status'])) {
			$data['shipping_cdek_status'] = $this->request->post['shipping_cdek_status'];
		} else {
			$data['shipping_cdek_status'] = $this->config->get('shipping_cdek_status');
		}

		if (isset($this->request->post['shipping_cdek_sort_order'])) {
			$data['shipping_cdek_sort_order'] = $this->request->post['shipping_cdek_sort_order'];
		} else {
			$data['shipping_cdek_sort_order'] = $this->config->get('shipping_cdek_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/cdek', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/cdek')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}