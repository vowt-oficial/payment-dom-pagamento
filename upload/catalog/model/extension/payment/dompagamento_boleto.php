<?php

class ModelExtensionPaymentDomPagamentoBoleto extends Model {

	public function getMethod($address, $total) {
		$this->load->model('extension/payment/dompagamento');

		if (!$this->model_extension_payment_dompagamento->enabledModule('boleto', $address, $total)) {
			return false;
		}

		$method_data = array(
			'code'       => 'dompagamento_boleto',
			'title'      => $this->config->get('payment_dompagamento_boleto_title')[$this->config->get('config_store_id')],
			'terms'      => '',
			'sort_order' => $this->config->get('payment_dompagamento_boleto_custom_sort_order')[$this->config->get('config_store_id')],
		);

		return $method_data;
	}

 	public function createTransaction(array $data = []): array {
		if (!empty($this->config->get('payment_dompagamento_boleto_expire')[$this->config->get('config_store_id')])) {
			$boleto_due_days = (int) $this->config->get('payment_dompagamento_boleto_expire')[$this->config->get('config_store_id')];
		} else {
			$boleto_due_days = 1;
		}

		if ($boleto_due_days <= 0) {
			$boleto_due_days = 1;
		}

		$data = [
			'boleto' => [
				'boleto_due_days' => $boleto_due_days,
			],
			'discount' => 0,
			'payment_method' => 'boleto',
		];

		$this->load->model('extension/payment/dompagamento');

		$response = $this->model_extension_payment_dompagamento->createTransaction($data);

		return (array) $response;
 	}

}