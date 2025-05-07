<?php

class ControllerExtensionPaymentDomPagamento extends Controller {

	public function initialize() {
		if ($this->config->get('payment_dompagamento_status') && $this->config->get('payment_dompagamento_store_status')[$this->config->get('config_store_id')]) {
			$this->document->addScript('catalog/view/javascript/jquery.card.js?v=1');
			$this->document->addScript('https://apiv3.dompagamentos.com.br/js/sdk-dompagamentos.min.js');
		}
	}

	public function teste() {
		$new_order_status_id = 1;
		$order_id = 11791;

		$this->load->model('extension/payment/dompagamento');
	
		$order_history_status_ids = $this->model_extension_payment_dompagamento->getOrderStatusIds($order_id);

		$order_history_status_ids = [ 1, 18 ];
	
		// Status configurados como "pagos"
		$payment_dompagamento_status_paid = $this->config->get('payment_dompagamento_status_paid');
	
		// Lista de status permitidos após pagamento
		$allowed_after_paid = [
			$this->config->get('payment_dompagamento_status_refunded'),
			$this->config->get('payment_dompagamento_status_chargeback'),
			$this->config->get('payment_dompagamento_status_in_mediation'),
			$this->config->get('payment_dompagamento_status_pending_refund'),
			$this->config->get('payment_dompagamento_status_dispute_pending'),
		];
	
		// Verifica se já passou por um status "pago"
		$status_pago_ja_passou = array_intersect($payment_dompagamento_status_paid, $order_history_status_ids);
	
		if (!empty($status_pago_ja_passou)) {
			// Se novo status NÃO for permitido após pagamento, bloqueia
			if (!in_array($new_order_status_id, $allowed_after_paid)) {
				pr("Status '{$new_order_status_id}' bloqueado após pagamento.");
				die;
			}
		}

		die('pode cadastrar');
	
		// Se chegou aqui, pode seguir com a atualização do status
	}

	public function teste__() {

		$json = '
		{
			"event": "CHARGE-APPROVED",
			  "signature": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ijc3MDAwNzM2LWMxODEtNGEyMy1iZTI1LTFlNWNjNTg2ZDA4ZCIsImV4cCI6MTc0MzU2NjU2M30.Zp97yMq7AseTjoWi_ijihhkJHz-ACadI4RurLtmPxMc",
			"data": {
			  "id": "77000736-c181-4a23-be25-1e5cc586d08d",
			  "created_at": "2025-04-01 13:01:53",
			  "updated_at": "2025-04-01 13:02:40",
			  "cod_external": "10544",
			  "amount": 10293,
			  "liquid_amount": 10162,
			  "refunds": {
				"details": [],
				"total_refunds": 0
			  },
			  "currency": "BRL",
			  "status": "paid",
			  "status_details": "Aprovado",
			  "msg": null,
			  "payment_method": "pix",
			  "card_code_auth": null,
			  "card_brand": null,
			  "card_bin": null,
			  "card_month": null,
			  "card_year": null,
			  "installments": "1",
			  "boleto_url": null,
			  "boleto_digitable_line": null,
			  "pix_qrcode": "data:image/png;base64, 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",
			  "pix_qrcode_url": "https://apiv3.dompagamentos.com.br/pix/qrcode/77000736-c181-4a23-be25-1e5cc586d08d",
			  "pix_content": "00020126820014br.gov.bcb.pix2560pix.treeal.com/qr/v3/at/7dfb0556-b612-485b-8cbd-db11220864065204000053039865802BR5919LOJA_DO_KIMONO_LTDA6008CASCAVEL62070503***6304F40E",
			  "pix_expire": "2025-04-02 14:01:51",
			  "items": [
				{
				  "id": "",
				  "externCode": "",
				  "description": "Rash Guard Masculina Flash",
				  "price": 0,
				  "quantity": 1,
				  "sku": "",
				  "gtin": ""
				},
				{
				  "id": "",
				  "externCode": "",
				  "description": "Rash Guard Masculina Flash",
				  "price": 0,
				  "quantity": 1,
				  "sku": "",
				  "gtin": ""
				},
				{
				  "id": "",
				  "externCode": "",
				  "description": "Frete fixo",
				  "price": 0,
				  "quantity": 1,
				  "sku": "",
				  "gtin": ""
				}
			  ],
			  "customer": {
				"name": "Arthur Vin\u00edcius",
				"email": "viniciusarthur513@gmail.com",
				"mobile_phone": "81981748772",
				"document": "14872822498",
				"document_type": "CPF",
				"birthdate": null,
				"gender": "",
				"address": {
				  "street": "Rua Maria Jos\u00e9 da Silva",
				  "number": "13",
				  "neighborhood": "Santo Ant\u00f4nio",
				  "zip_code": "55750000",
				  "city": "Surubim",
				  "state": "Pernambuco",
				  "complement": null
				}
			  },
			  "shipping": [],
			  "fee_details": {
				"amount": 2.29,
				"fee_payer": "collector",
				"type": "dompagamentos_fee"
			  },
			  "metadata": "null",
			  "query_param": null,
			  "postbackUrl": "https://lojadokimono.com/index.php?route=extension/payment/dompagamento/callback",
			  "relations": {
				"id_invoice": "",
				"id_link_payment": "",
				"id_subscriber": ""
			  }
			}
		  }
		';

		$post = json_decode($json, true);
		
		require_once DIR_SYSTEM . 'library/adhocore-jwt/vendor/autoload.php';

		try {
			// $post = json_decode(file_get_contents('php://input'), true);

			if (empty($post)) {
				throw new \Exception('Invalid post data');
			}

			if (empty($post['signature'])) {
				throw new \Exception('Invalid signature');
			}


			//Assinatura recebida pelo webhook:
			$signature = $post['signature'];

			//Seu token da API utilizado para enviar a transação:
			$token_api = $this->config->get('payment_dompagamento_secret_key')[$this->config->get('config_store_id')];

			$jwt = new \Ahc\Jwt\JWT($token_api, 'HS256');

			$payload = $jwt->decode($signature, false); // `false` para não verificar a assinatura por enquanto

			// Verifique manualmente a expiração
			if (isset($payload['exp'])) {
				$expirationTime = $payload['exp'];
				$currentTime = time(); // Hora atual
		
				// Se o token estiver expirado
				if ($currentTime > $expirationTime) {
					// Se o token estiver expirado, trate isso de forma personalizada
					$this->log->write("Token expirado manualmente.");
					// Você pode continuar o processamento ou registrar o erro
				}
			}

			die('passou');

		} catch (\Ahc\Jwt\Exception\ExpiredException $e) {
			// Aqui o token expirado será aceito se estiver dentro do leeway
			$this->log->write("Token expirado, mas aceitando: " . print_r($post, true));
		} catch (Exception $e) {
			// Captura de qualquer outro erro
			echo 'Caught exception: ', $e->getMessage(), "\n";
			$this->log->write('Caught exception - ' . print_r($post, true));
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

			$payload = $jwt->decode($signature, false);

			$this->load->model('extension/payment/dompagamento');

			$this->model_extension_payment_dompagamento->addOrderHistory($post['data']);

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