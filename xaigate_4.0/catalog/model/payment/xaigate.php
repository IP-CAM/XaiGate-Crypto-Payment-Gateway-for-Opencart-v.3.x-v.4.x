<?php
namespace Opencart\Catalog\Model\Extension\Xaigate\Payment;
class Xaigate extends \Opencart\System\Engine\Model {
	
	public function getMethods($address, $total=0) {
		$title = $this->config->get('payment_xaigate_title');

		$option_data['xaigate'] = [
			'code' => 'xaigate.xaigate',
			'name' => $title
		];
		$method_data = array(
			'code'       => 'xaigate',
			'title'      => $title,
			'name'       => $title,
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_xaigate_sort_order')
		);
		return $method_data;
	}
	
}