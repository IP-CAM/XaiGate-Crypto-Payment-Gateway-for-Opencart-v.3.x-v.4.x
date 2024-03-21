<?php
namespace Opencart\Admin\Controller\Extension\Xaigate\Payment;
class Xaigate extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $separator = '';
	
	public function __construct($registry) {
        parent::__construct($registry);
    }
	
	public function index(): void {


		$this->load->language('extension/xaigate/payment/xaigate');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')  && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_xaigate', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$arr = array("warning", "merchant_id", "apikey");
		foreach ($arr as $v) $data['error_' . $v] = (isset($this->error[$v])) ? $this->error[$v] : "";

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/xaigate/payment/xaigate', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/xaigate/payment/xaigate', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$array_data = array(
			'payment_xaigate_apikey',
			'payment_xaigate_merchant_id',
			'payment_xaigate_title',
			'payment_xaigate_order_status_id',
			'payment_xaigate_status',
			'payment_xaigate_sort_order',
		);

		foreach ($array_data as $v) {
			$data[$v] = (isset($this->request->post[$v])) ? $this->request->post[$v] : $this->config->get($v);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/xaigate/payment/xaigate', $data));
	}
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/xaigate/payment/xaigate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_xaigate_apikey']) {
			$this->error['apikey'] = $this->language->get('error_apikey');
		}
		return (!$this->error) ? true : false;
	}
}