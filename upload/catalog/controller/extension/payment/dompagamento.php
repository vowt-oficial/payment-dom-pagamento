<?php

class ControllerExtensionPaymentDomPagamento extends Controller {

	public function initialize() {
		if ($this->config->get('payment_dompagamento_status') && $this->config->get('payment_dompagamento_store_status')[$this->config->get('config_store_id')]) {
			$this->document->addScript('catalog/view/javascript/jquery.card.js?v=1');
			$this->document->addScript('https://apiv3.dompagamentos.com.br/js/sdk-dompagamentos.min.js');
		}
	}

	public function callback() {
		try {
			$post = json_decode(file_get_contents('php://input'), true);

			if (empty($post)) {
				throw new \Exception('Invalid post data');
			}

			if (empty($post['signature'])) {
				throw new \Exception('Invalid signature');
			}

			require_once DIR_SYSTEM . 'library/adhocore-jwt/vendor/autoload.php';

			//Assinatura recebida pelo webhook:
			$signature = $post['signature'];

			//Seu token da API utilizado para enviar a transação:
			$token_api = $this->config->get('payment_dompagamento_secret_key')[$this->config->get('config_store_id')];

			$jwt = new \Ahc\Jwt\JWT($token_api, 'HS256');

			$payload = $jwt->decode($signature);

			$this->load->model('extension/payment/dompagamento');

			$this->model_extension_payment_dompagamento->addOrderHistory($post['data']);

			// $this->log->write(print_r($post, true));

		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->log->write('Caught exception - ' . print_r($post, true));
		}
	}

	public function headerTemplate() {
        $data['env'] = $this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')];

        return $this->load->view('extension/payment/dompagamento_header_template', $data);
	}

	public function status() {
		$json = [];

		try {

			$this->load->model('account/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_account_order->getOrder($order_id);

			if (!$order_info) {
				throw new \Exception('Pedido inválido');
			}

			$this->load->model('extension/payment/dompagamento');

			$transaction = $this->model_extension_payment_dompagamento->getTransactionByOrderId($order_id);

			if (!$transaction) {
				throw new \Exception('Pedido inválido');
			}

			$json['status'] = $transaction['status'];
		} catch (\Exception $e) {
			//throw $th;
			$json['error'] = $e->getMessage();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}