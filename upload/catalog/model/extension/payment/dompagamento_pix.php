<?php

class ModelExtensionPaymentDomPagamentoPIX extends Model {

	public function getMethod($address, $total) {

		$this->load->model('extension/payment/dompagamento');

		if (!$this->model_extension_payment_dompagamento->enabledModule('pix', $address, $total)) {
			return false;
		}

		$method_data = array(
			'code'       => 'dompagamento_pix',
			'title'      => $this->config->get('payment_dompagamento_pix_title')[$this->config->get('config_store_id')],
			'terms'      => '',
			'sort_order' => $this->config->get('payment_dompagamento_pix_custom_sort_order')[$this->config->get('config_store_id')]
		);

		return $method_data;
	}

	public function createTransaction(array $data = []): array {
		// Verifica a configuração para definir a expiração do PIX
		$pix_expireMinutes = $this->getPixExpirationMinutes();

		// Criação dos dados para a transação
		$transactionData = [
			"pix" => [
				"pix_expire" => (new DateTime(" + {$pix_expireMinutes} MINUTE "))->format('Y-m-d H:i:s'),
			],
			'discount' => 0,
			"payment_method" => "pix",
		];
	
		// Carrega o modelo necessário
		$this->load->model('extension/payment/dompagamento');
	
		// Chama o método para criar a transação
		$response = $this->model_extension_payment_dompagamento->createTransaction($transactionData);
	
		return (array) $response;
	}
	
	private function getPixExpirationMinutes(): int {
		// Verifica se a configuração existe e tem um valor válido
		$pix_expire = (int) $this->config->get('payment_dompagamento_pix_days')[$this->config->get('config_store_id')] ?? 0;
	
		// Se o valor for inválido ou não existir, usa o valor padrão de 30 minutos
		if ($pix_expire <= 0) {
			$pix_expire = 30;
		}
	
		return $pix_expire;
	}

}