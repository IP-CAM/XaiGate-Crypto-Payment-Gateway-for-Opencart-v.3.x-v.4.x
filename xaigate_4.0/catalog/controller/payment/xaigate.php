<?php
namespace Opencart\Catalog\Controller\Extension\Xaigate\Payment;
class Xaigate extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $separator = '';
		
	public function __construct($registry) {
		parent::__construct($registry);
	}

	public function index() 
	{
		$this->language->load('extension/xaigate/payment/xaigate');
		$order_id = $this->session->data['order_id'];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$shop_name = $this->config->get('payment_xaigate_shop_name');
		$apikey = $this->config->get('payment_xaigate_apikey');
		$amount = number_format($order_info['total'], 2, '.', '');
		$desc = $this->language->get('order_description') . $order_id;
		$email = $order_info['email'];
		$store_url = $order_info['store_url'];

		$data_request = [
			'shopName'	=> $shop_name,
			'amount'	=> $amount,
			'currency'	=> $this->session->data['currency'],
			'orderId'	=> $order_id,
			'email'		=> $email,
			'apiKey'	=> $apikey,
			'successUrl'=> $this->url->link('extension/xaigate/payment/xaigate.response', '', 'true'), //$store_url . 'index.php?route=extension/payment/xaigate/response',
			'failUrl'   => $this->url->link('extension/xaigate/payment/xaigate.response', 'fail=1', 'true'), //$store_url . 'index.php?route=extension/payment/xaigate/response&fail=1',
			'notifyUrl' => $this->url->link('extension/xaigate/payment/xaigate.callback', '', 'true'), // $store_url . 'index.php?route=extension/payment/xaigate/callback',
			'description' => $desc
		];

		$headers = [
			'Content-Type: application/json'
		];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data_request));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, 'https://wallet-api.xaigate.com/api/v1/invoice/create');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);

		curl_close($curl);

		$json_data = json_decode($result, true);
		$data['url'] = $json_data['payUrl'];

		$this->load->model('checkout/order');
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/xaigate/payment/xaigate')) {
			return $this->load->view($this->config->get('config_template') . '/template/extension/xaigate/payment/xaigate', $data);
		} else {
			return $this->load->view('extension/xaigate/payment/xaigate', $data);
		}
	}
	
	public function response() 
	{
		$this->load->model('checkout/order');

		if (isset($this->request->get['fail']) AND $this->request->get['fail']) {
			$this->response->redirect($this->url->link('checkout/confirm', '', true));
		} else {
			$this->cart->clear();
			$this->response->redirect($this->url->link('checkout/success', '', true));
		}
	}

	public function callback()
	{
		$order_id = isset($this->request->post['orderId'])
            ? (int) $this->request->post['orderId']
            : 0;
			
		if (!$order_id) {
            exit;
        }

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$amount = number_format($order_info['total'], 2, '.', '');
		$comment = 'XaiGate Transaction id: ' . $this->request->post['invoiceId'];
		$this->model_checkout_order->addHistory($order_id, $this->config->get('payment_xaigate_order_status_id'), $comment, true);
	}
	
}