<?php

class ModelExtensionPaymentDomPagamentoCard extends Model {

	public function getMethod($address, $total) {

		$this->load->model('extension/payment/dompagamento');

		if (!$this->model_extension_payment_dompagamento->enabledModule('card', $address, $total)) {
			return false;
		}

		$method_data = array();

		$method_data = array(
			'code'       => 'dompagamento_card',
			'title'      => $this->config->get('payment_dompagamento_card_title')[$this->config->get('config_store_id')],
			'terms'      => '',
			'sort_order' => $this->config->get('payment_dompagamento_card_custom_sort_order')[$this->config->get('config_store_id')],
		);

		return $method_data;
	}

 	public function createCardToken($data) {

	    $this->load->model('extension/payment/dompagamento');

	    $this->load->model('checkout/order');

	    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

	    if (isset($order_info['custom_field'][$this->config->get('payment_dompagamento_cpf_id')])) {
	    	$document = $order_info['custom_field'][$this->config->get('payment_dompagamento_cpf_id')];
	    } else {
	    	$document = $order_info['custom_field'][$this->config->get('payment_dompagamento_cnpj_id')];
	    }
		
		list($expMonth, $expYear) = explode('/', $data['card_expiry']);

		$data = [
			"card" => [
				"number" =>  preg_replace('/[^0-9]/', '', $data['number']),
				"exp_month" => preg_replace('/[^0-9]/', '', $expMonth),
				"exp_year" => preg_replace('/[^0-9]/', '', $expYear),
				"holder_document" => preg_replace('/[^0-9]/', '', $document),
				"holder_name" => $data['card_name'],
				"cvv" => preg_replace('/[^0-9]/', '', $data['card_cvv']),
				"type" => "card",
				"billing_address" => [
					"line_1" => $order_info['payment_address_1'],
					"line_2" => $order_info['payment_address_2'],
					"zip_code" => preg_replace('/[^0-9]/', '', $order_info['payment_postcode']),
					"city" => $order_info['payment_city'],
					"state" => $order_info['payment_zone_code'],
					"country" => $order_info['payment_iso_code_2'],
				],				
			],
		];

		// $teste = $this->httpDelete("https://hubapi.Dom Pagamentos/core/v1/customers/cus_dYaeqmLCLFG3R0AK/cards/card_gROm93Bs7sON2dZv");
		
		unset($this->session->data['dompagamento_card_id'], $this->session->data['dompagamento_card_token']);
		
		$key = $this->config->get('payment_dompagamento_account_public_key');
		
		pr($data);die;
		
		$response = $this->httpPost('https://api.Dom Pagamentos/core/v5/tokens?appId=' . $key, $data);

		if ( isset($this->request->post['save']) && $this->config->get('payment_dompagamento_save')[$this->config->get('config_store_id')]) {
			$token = $this->saveCard($response['id'], $data['card']);

			$this->session->data['dompagamento_card_id'] = $token;
			$type = 'card_id';
		} else {
			$this->session->data['dompagamento_card_token'] = $response['id'];

			$type = 'card_token';
			$token = $response['id'];
		}

		return [
			'type' => $type,
			'token' => $token,
		];
 	}

	public function saveCard($card_token, $data) {
		
	    $this->load->model('extension/payment/dompagamento');

		$customer_info = $this->model_extension_payment_dompagamento->getCustomer(true);

		$data['token'] = $card_token;

		$response = $this->httpPost('https://hubapi.Dom Pagamentos/core/v1/customers/'. $customer_info['id'] .'/cards', $data);

		if (isset($this->request->post['save'])) { 
			$show_card = true;
		} else {
			$show_card = false;
		}

		// Salvamos o cartao ou atualizamos
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."dompagamento_card WHERE store_id='".(int)$this->config->get('config_store_id')."' AND customer_id='".(int)$this->customer->getId()."' AND card_id='".$this->db->escape($response['id'])."' AND mode='". $this->db->escape($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]) ."' ");

		if ($query->num_rows) {
			$this->db->query("UPDATE ".DB_PREFIX."dompagamento_card SET show_card='". (int)$show_card ."', customer_id='".(int)$this->customer->getId()."', card_id='".$this->db->escape($response['id'])."', data='".$this->db->escape(json_encode($response))."', mode='". $this->db->escape($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]) ."' WHERE dompagamento_card_id='".(int)$query->row['dompagamento_card_id']."' ");

			$dompagamento_card_id = $query->row['dompagamento_card_id'];

		} else {
			$this->db->query("INSERT INTO ".DB_PREFIX."dompagamento_card SET store_id='".(int)$this->config->get('config_store_id')."', show_card='". (int)$show_card ."', customer_id='".(int)$this->customer->getId()."', card_id='".$this->db->escape($response['id'])."', data='".$this->db->escape(json_encode($response))."', mode='". $this->db->escape($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]) ."' ");

			$dompagamento_card_id = $this->db->getLastId();
		}

		return $dompagamento_card_id;
	}

 	public function createTransaction(array $data) {

		$data = [
			'credit_card' => [
				'installments' => (int)$data['installments'],
				'holder_name' => (string)$data['holder_name'],
				'token' => (string)$data['token'],
				// 'bin' => $data['bin'],
				'bin' => preg_replace('/[^0-9]/', '', $data['bin']),
				'brand' => (string)strtolower($data['brand']),
			],
			'discount' => 0,
			'payment_method' => 'credit_card',
		];

		$this->load->model('extension/payment/dompagamento');

		$response = $this->model_extension_payment_dompagamento->createTransaction($data);

		return (array) $response;

		$payments = [
			[
				"credit_card" => [
					"card" => [
						"billing_address" => [
							"line_1" => $order_info['payment_address_1'],
							"line_2" => $order_info['payment_address_2'],
							"zip_code" => preg_replace('/[^0-9]/', '', $order_info['payment_postcode']),
							"city" => $order_info['payment_city'],
							"state" => $order_info['payment_zone_code'],
							"country" => $order_info['payment_iso_code_2'],
						],
					],
					"statement_descriptor" => $this->config->get('config_invoice_prefix'), 
					"operation_type" => $this->config->get('payment_dompagamento_card_operation_type')[$this->config->get('config_store_id')],
					"installments" => $data['installments'],
				],
				"payment_method" => "credit_card",
				"amount" => (int)($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100),
			],
		];

		// if (!empty($this->session->data['dompagamento_card_id'])) {
		// 	$card_info = $this->getCard($this->session->data['dompagamento_card_id']);

		// 	if (!$card_info) {
		// 		throw new \Exception('O cartão informado não é valido');
		// 	}

		// 	$payments[0]['credit_card']['card_id'] = $card_info['card_id'];
		// } else {
		// 	$payments[0]['credit_card']['card_token'] = $this->session->data['dompagamento_card_token'];
		// }

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

		return $this->createTransaction($payments);
 	}

	 public function getInstallments($order_id) {
		$this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($order_id);

		$installments = [];

        foreach (range(1, $this->config->get('payment_dompagamento_card_max_installments')[$this->config->get('config_store_id')]) as $installment) {
            if (!empty($this->config->get('payment_dompagamento_card_installment')[$this->config->get('config_store_id')][$installment])) {
                $interest = (float) $this->config->get('payment_dompagamento_card_installment')[$this->config->get('config_store_id')][$installment];
            } else {
                $interest = 0;
            }

            $value = (float) $order_info['total'] / $installment;

            $fee = (($value * $interest) / 100);
            
            // Validamos a parcela minima
            if ($value <= $this->config->get('payment_dompagamento_card_min_installment')[$this->config->get('config_store_id')]) {
                continue;
            }

            $installments[] = [
                'installment' => $installment,
                'fee' => $fee,
                'value' => $value,
                'interest' => $interest,
                'value_interest' => $this->currency->format($value + (($value * $interest) / 100), $order_info['currency_code']),
                'total' => $this->currency->format( $order_info['total'] + $fee, $order_info['currency_code']),
            ];
        }

		return $installments;
	}
}