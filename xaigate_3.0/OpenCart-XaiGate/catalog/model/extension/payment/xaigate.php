<?php 
class ModelExtensionPaymentXaiGate extends Model {
	public function getMethod($address, $total) {
		$title = $this->config->get('payment_xaigate_title');

		return array(
			'code' => 'xaigate',
			'terms' => '',
			'title' => ($title ? $title : 'xaigate'),
			'sort_order' => $this->config->get('payment_xaigate_sort_order')
		);
	}
}
?>