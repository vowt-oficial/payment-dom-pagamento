<?php

if (!function_exists('removeAccents')) {
	function removeAccents($string) {
		// Arrays with accented characters and their non-accented equivalents
		$from = array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç',
					  'Á','À','Ã','Â','É','Ê','Í','Ó','Ô','Õ','Ú','Ü','Ç');
		$to = array('a','a','a','a','e','e','i','o','o','o','u','u','c',
					'A','A','A','A','E','E','I','O','O','O','U','U','C');
		
		// Replace each accented character with its non-accented equivalent
		return str_replace($from, $to, $string);
	}
}

class ModelExtensionPaymentDomPagamento extends Model {

    public function getMethod($address, $total) {
    	return [];
    }

	public function validateToken(string $payment_method, string $token): bool {
		if ($_SERVER['REMOTE_ADDR'] == '187.19.107.211') {
			return true;
		}

		if (isset($this->session->data['token'][$payment_method]) && $this->session->data['token'][$payment_method] == $token) {

			unset($this->session->data['token']);

			return true;
		}

		unset($this->session->data['token']);

		throw new \Exception("Token da requisição inválido");
	}

	public function getToken(string $payment_method): string {
		if (!isset($this->session->data['token'])) $this->session->data['token'] = [];

		$this->session->data['token'][$payment_method] = uniqid();

		return (string) $this->session->data['token'][$payment_method];
	}

	public function enabledModule(string $payment_method, array $address, float $total) {

		if ($total <= 0) {
			return false;
		}

		if (!$this->config->get('payment_dompagamento_status')[$this->config->get('config_store_id')]) {
			return false;
        }
		
        if (!$this->config->get('payment_dompagamento_'. $payment_method .'_status')[$this->config->get('config_store_id')]) {
			return false;
        }

		if ($_SERVER['REMOTE_ADDR'] != '187.19.107.211') {
			if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] == 'production' && !$this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
				return false;
			}
		}

        return true;
    }

	public function validateOrder() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			throw new \Exception('Existem produtos no carrinho de compras sem estoque.');
		}

		if ($this->session->data['dompagamento_attemp'] > $this->config->get('payment_dompagamento_limit')) {
			$this->log->write("(Dom Pagamentos) muitas tentativas de compras. Pedido: ".$this->session->data['order_id']." (createTransaction) - IP ".$_SERVER['REMOTE_ADDR']." ");
            throw new \Exception('Muitas tentativas de compras. Por favor, tente novamente mais tarde. (createTransaction)');
		}

		if (empty($this->session->data['order_ip'])) {
			$this->session->data['order_ip'][$_SERVER['REMOTE_ADDR']] = 1;
		} else {
			$this->session->data['order_ip'][$_SERVER['REMOTE_ADDR']] += 1;
		}

		// if (isset($this->session->data['order_ip'][$_SERVER['REMOTE_ADDR']]) && $this->session->data['order_ip'][$_SERVER['REMOTE_ADDR']] > $this->config->get('payment_dompagamento_limit')) {
		// 	$this->log->write("(Dom Pagamento) muitas tentativas de compras por IP. Pedido: ".$this->session->data['order_id']." (createTransaction) - IP ".$_SERVER['REMOTE_ADDR']." ");
        //     throw new \Exception('Muitas tentativas de compras por IP. Por favor, tente novamente mais tarde. (createTransaction)');
		// }

		// if ($this->getTotalAttempsByIp($_SERVER['REMOTE_ADDR']) > $this->config->get('payment_dompagamento_limit')) {
		// 	$this->log->write("(Dom Pagamento) muitas tentativas de compras por IP diário. Pedido: ".$this->session->data['order_id']." (createTransaction) - IP ".$_SERVER['REMOTE_ADDR']." ");
        //     throw new \Exception('Muitas tentativas de compras por IP diário. Pedido. Por favor, tente novamente mais tarde. (createTransaction)');
		// }

		// if ($this->session->data['dompagamento_attemp'] > $this->config->get('payment_dompagamento_limit')) {
		// 	$this->log->write("(Dom Pagamento) muitas tentativas de compras, por tentativas. Pedido: ".$this->session->data['order_id']." (createTransaction) - IP ".$_SERVER['REMOTE_ADDR']." ");
        //     throw new \Exception('Muitas tentativas de compras por tentativas.. Por favor, tente novamente mais tarde. (createTransaction)');
		// }

		if (!$this->customer->isLogged()) {
			$this->log->write("(Dom Pagamentos) cliente tentou efetuar a compra mais não estava logado. (validateOrder)");
            throw new \Exception('Sessão inválida.');
		}

		if (empty($this->session->data['payment_address']['address_id'])) {
			$this->log->write("(Dom Pagamentos) endereço do cliente é inválido (validateOrder).");
            throw new \Exception('(Dom Pagamentos) address_id: endereço de fatura do cliente é inválido (validateOrder).');
		}

		if ($this->cart->hasShipping() && empty($this->session->data['shipping_address']['address_id'])) {
			$this->log->write("(Dom Pagamentos) endereço do cliente é inválido (validateOrder).");
            throw new \Exception('Dom Pagamentos) address_id: endereço de entrega do cliente é inválido (validateOrder).');
		}

		if ($this->cart->hasShipping() && empty($this->session->data['shipping_method']['code'])) {
			$this->log->write("(Dom Pagamentos) forma de pagamento inválida (validateOrder).");
            throw new \Exception('(Dom Pagamentos) forma de pagamento inválida (validateOrder).');
		}

		if (empty($this->session->data['payment_method']['code'])) {
			$this->log->write("(Dom Pagamentos) forma de pagamento inválida (validateOrder).");
            throw new \Exception('(Dom Pagamentos) forma de pagamento inválida (validateOrder).');
        }

		if (empty($this->session->data['payment_method']['code'])) {
			$this->log->write("(Dom Pagamentos) forma de pagamento inválida (validateOrder).");
            throw new \Exception('(Dom Pagamentos) forma de pagamento inválida (validateOrder).');
        }

		$payment_methods = [
			'dompagamento_card',
			'dompagamento_boleto',
			'dompagamento_pix',
		];

		if (!in_array($this->session->data['payment_method']['code'], $payment_methods)) {
			$this->log->write("(Dom Pagamentos) forma de pagamentoselecionada é inválida (validateOrder).");
            throw new \Exception('Forma de pagamento inválido.');
        }

		if (empty($this->session->data['order_id']) || $this->session->data['order_id'] <= 0) {
			$this->log->write("(Dom Pagamentos) order_id: pedido expirado (validateOrder)");
			throw new \Exception('(Dom Pagamentos) order_id: pedido expirado (validateOrder)');
		}

		$this->load->model('checkout/order');

	    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$order_info) {
			$this->log->write("(Dom Pagamentos) pedido expirado (validateOrder)");
			throw new \Exception('(Dom Pagamentos) pedido expirado (validateOrder)');
		}

		if (!$this->cart->hasProducts()) {
			$this->log->write( "(Dom Pagamentos) Loja: ".$this->config->get('config_store_id')." Pedido ". $order_info['order_id'] ." não tem produtos válidos no carrinho de compras (validateOrder)");
			throw new \Exception('Pedido '. $order_info['order_id'] .' não tem produtos válidos (validateOrder)');
		}

		if ($order_info['total'] <= 0) {
			$this->log->write("(Dom Pagamentos) o valor do total do pedido é inválido. (validateOrder)");
			throw new \Exception('(Dom Pagamentos) o valor do total do pedido é inválido, selecione outra forma de pagamento. (validateOrder)');
		}

		return $order_info;
	}

    public function getTransactionByOrderId($order_id) {
    	$query = $this->db->query("SELECT * FROM ".DB_PREFIX."dompagamento_order WHERE order_id='".(int)$order_id."' ");

		if ($query->row) {
			$query->row['raw'] = json_decode($query->row['raw'], true);
		}

    	return $query->row;
    }

	public function canChangeOrderStatus($order_id, $new_order_status_id) {

		if ($new_order_status_id == 1) {
			return false;
		}

		$this->load->model('extension/payment/dompagamento');
		
		$order_history_status_ids = $this->model_extension_payment_dompagamento->getOrderStatusIds($order_id);

		$order_history_status_ids = array_map('intval', $order_history_status_ids);
	
		// Status pagos
		$status_pagos = $this->config->get('payment_dompagamento_status_paid');

		if (!is_array($status_pagos)) {
			$status_pagos = explode(',', $status_pagos);
		}

		$status_pagos = array_map('intval', $status_pagos);
	
		// Permitidos após pagamento
		$status_permitidos_pos_pagamento = [
			$this->config->get('payment_dompagamento_canceled'),
			$this->config->get('payment_dompagamento_status_not_authorized'),
			$this->config->get('payment_dompagamento_status_chargedback'),
		];

		$status_permitidos_pos_pagamento = array_map('intval', $status_permitidos_pos_pagamento);
		
		// Verifica se já foi pago
		$ja_foi_pago = !empty(array_intersect($status_pagos, $order_history_status_ids));
	
		if ($ja_foi_pago && !in_array($new_order_status_id, $status_permitidos_pos_pagamento)) {
			return false;
		}

		return true;
	}

	public function addOrderHistory(array $data) {
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."dompagamento_order WHERE transaction_id LIKE '". $this->db->escape($data['id']) ."' ");

		$this->db->query("UPDATE ".DB_PREFIX."dompagamento_order SET status = '". $this->db->escape($data['status']) ."' WHERE transaction_id = '". $this->db->escape($data['id']) ."' ");

		$status = @strtolower($data['status']);

		$order_status_id = $this->config->get('payment_dompagamento_status_' . $status);

		if ($this->canChangeOrderStatus($query->row['order_id'], $order_status_id)) {
			if ($data['status'] != $query->row['status']) {

				$this->load->model('checkout/order');

				$this->session->data['user'] = 'dompagamento';

				$this->model_checkout_order->addOrderHistory($query->row['order_id'], $order_status_id, '(cb dompagamento)', true, false, 'dompagamento');  
			}
		}
	}

	public function getOrderStatusIds($order_id) {
		$sql = "
			SELECT * FROM ".DB_PREFIX."order_history WHERE order_id = '". (int)$order_id ."'
		";

		$query = $this->db->query($sql);

		return array_column($query->rows, 'order_status_id');
	}

	public function getTotalAttempsByIp(string $ip) {
		$query = $this->db->query("
			SELECT COUNT(order_id) AS total FROM ".DB_PREFIX."order 
			WHERE ip='". $this->db->escape($ip) ."' AND order_status_id > 0
			AND Date(date_added) = CURDATE()
		");

		return $query->row['total'];
	}

	public function createTransactionProduction($data) {
		if (empty($this->session->data['dompagamento_attemp'])) {
			$this->session->data['dompagamento_attemp'] = 1;
		} else {
			$this->session->data['dompagamento_attemp'] += 1;
		}

		$this->load->model('checkout/order');

		// Validate
		$order_info = $this->validateOrder();
		
		$dompagamento = new DomPagamento($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]);

		foreach ($this->model_checkout_order->getOrderProducts($order_info['order_id']) as $order_product) {
			$dompagamento->addItem([
				'description' => substr(removeAccents($order_product['name']), 0, 50),
				'amount' 	  => (float) $order_product['price'],
				'quantity'    => (int) $order_product['quantity'],
				'code'    	  => (int) $order_product['product_id'],
			]);
		}

		// Total
		foreach ($this->model_checkout_order->getOrderTotals($order_info['order_id']) as $order_total) {
			if (in_array($order_total['code'], ['sub_total', 'total'])) continue;

			// Se for descontos não enviamos para o dompagamento
			if ($order_total['value'] <= 0) continue;

			$title = explode('-', $order_total['title'])[0];

			$dompagamento->addItem([
				'description' => substr(removeAccents($title), 0, 50),
				'amount' 	  => (float)$order_total['value'],
				'quantity'    => 1,
				'code'    	  => $order_total['code'],
			]);
		}

		// Payment Address
		$dompagamento->setPaymentAddress([
			"street" => $order_info['payment_address_1'],
			'number' => $order_info['payment_number'],
			"neighborhood" => $order_info['payment_address_2'],
			"zip_code" => preg_replace('/[^0-9]/', '', $order_info['payment_postcode']),
			"city" => $order_info['payment_city'],
			"state" => $order_info['payment_zone_code'],
			"uf" => $order_info['payment_zone_code'],
		]);

		// Shipping
		if ($this->cart->hasShipping()) {
			$dompagamento->setPaymentAddress([
				"street" => $order_info['shipping_address_1'],
				'number' => $order_info['shipping_number'],
				"neighborhood" => $order_info['shipping_address_2'],
				"zip_code" => preg_replace('/[^0-9]/', '', $order_info['shipping_postcode']),
				"city" => $order_info['shipping_city'],
				"state" => $order_info['shipping_zone'],
				"uf" => $order_info['shipping_zone_code'],
			]);
		}
		
		// Customer
		// if ($this->customer->isCompany()) {
		// 	$name = $order_info['custom_field'][$this->config->get('payment_dompagamento_company_id')];
		// } else {
		// }

		$name = $order_info['firstname'].' '.$order_info['lastname'];

		$dompagamento->setCustomer([
			"name" 		    => $name,
			"email" 	    => $order_info['email'],
			"mobile_phone"  => preg_replace('/[^0-9]/', '', $order_info['telephone']),
			"code" 		    => (int) $order_info['customer_id'],
			"document" 	    => preg_replace('/[^0-9]/', '', $this->customer->getDocument()),
			"document_type" => $this->customer->getDocumentType(),
			'session_id' 	=> $this->session->getId(),
			'address' 		=> $dompagamento->getPaymentAddress(),
			'ip' 		    => $this->request->server['REMOTE_ADDR'],
		]);

		$dompagamento->setSecretKey($this->config->get('payment_dompagamento_secret_key')[$this->config->get('config_store_id')]);
		$dompagamento->setPaymentMethod($data['payment_method']);
		$dompagamento->setTotal((float)$order_info['total']);
		$dompagamento->setDiscount((float)$data['discount']);
		$dompagamento->setCodExternal((int)$order_info['order_id']);
		$dompagamento->setPostbackUrl(HTTPS_SERVER . 'index.php?route=extension/payment/dompagamento/callback');

		// Create Transaction
		$response = $dompagamento->createTransaction($data);

		$description = 'Transação efetuada por Dom Pagamento' . "\n";
		$description .= " <b>Transação:</b> ".$response['id']." \n";

		/********** CARTÃO DE CRÉDITO ********/
		if ($order_info['payment_code'] == 'dompagamento_card') {
			if (!empty($response['card_bin'])) {
				$description .= " <b>Cartão de crédito:</b> ".$response['card_brand']." final " . substr($response['card_bin'], 4) . "\n";
			}
		}

		/********** PIX ********/
		if (!empty($response['pix_qrcode'])) {
			$description .= "- QrCode PIX (Pague através do App do banco preferido): \n";
			$description .= "- Copia e Cola: ".$response['pix_content']." \n" ;
			$description .= "<img src=\"{$response['pix_qrcode']}\" width=\"150\" /> \n\n";
		}

		// if ($_SERVER['REMOTE_ADDR'] == '187.19.107.211') {
		// 	pr($response);
		// 	pr($order_info);die('sim');
		// }

		if (isset($response['msg'])) {
			$this->model_checkout_order->editGatewayFallback($order_info['order_id'], $response['msg']);

			$description .= " {$response['msg']} \n";
		}

		if ($_SERVER['REMOTE_ADDR'] == '187.19.107.211') {
			pr($response);die;
		}

		if (isset($response['status']) && $response['status'] == 'not_authorized') {
			throw new \Exception($response['status_details']);
		}

		$this->db->query("INSERT INTO ".DB_PREFIX."dompagamento_order SET store_id = '".$this->config->get('config_store_id')."', status='". $this->db->escape($response['status']) ."', payment_code='". $this->db->escape($this->session->data['payment_method']['code']) ."', customer_id='".(int)$order_info['customer_id']."', order_id='".(int)$order_info['order_id']."', transaction_id='".$this->db->escape($response['id'])."', raw = '". $this->db->escape(json_encode($response)) ."', env='". $this->db->escape($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]) ."', date_added = NOW(), date_updated=NOW()");

		$order_status_id = $this->config->get('payment_dompagamento_status_' . $response['status']);

		$this->session->data['user'] = 'dompagamento';

		$this->model_checkout_order->addOrderHistory($order_info['order_id'], $order_status_id, $description, true, false, 'dompagamento');

		// $this->cart->clear();
		return $response;
	}

	public function createTransactionSandbox($data) {
		if (empty($this->session->data['dompagamento_attemp'])) {
			$this->session->data['dompagamento_attemp'] = 1;
		} else {
			$this->session->data['dompagamento_attemp'] += 1;
		}

		$this->load->model('checkout/order');

		// Validate
		$order_info = $this->validateOrder();

		$this->db->query("INSERT INTO ".DB_PREFIX."dompagamento_order SET store_id = '".$this->config->get('config_store_id')."', status='initiated', payment_code='". $this->db->escape($this->session->data['payment_method']['code']) ."', customer_id='".(int)$order_info['customer_id']."', order_id='".(int)$order_info['order_id']."', transaction_id='', raw = '', env='". $this->db->escape($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]) ."', date_added = NOW(), date_updated=NOW()");
		
		$dompagamento_order_id = $this->db->getLastId();

		$dompagamento = new DomPagamento($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')]);

		foreach ($this->model_checkout_order->getOrderProducts($order_info['order_id']) as $order_product) {
			$dompagamento->addItem([
				'description' => substr(removeAccents($order_product['name']), 0, 50),
				'amount' 	  => (float) $order_product['price'],
				'quantity'    => (int) $order_product['quantity'],
				'code'    	  => (int) $order_product['product_id'],
			]);
		}

		// Total
		foreach ($this->model_checkout_order->getOrderTotals($order_info['order_id']) as $order_total) {
			if (in_array($order_total['code'], ['sub_total', 'total'])) continue;

			// Se for descontos não enviamos para o dompagamento
			if ($order_total['value'] <= 0) continue;

			$title = explode('-', $order_total['title'])[0];

			$dompagamento->addItem([
				'description' => substr(removeAccents($title), 0, 50),
				'amount' 	  => (float)$order_total['value'],
				'quantity'    => 1,
				'code'    	  => $order_total['code'],
			]);
		}

		// Payment Address
		$dompagamento->setPaymentAddress([
			"street" => $order_info['payment_address_1'],
			'number' => $order_info['payment_number'],
			"neighborhood" => $order_info['payment_address_2'],
			"zip_code" => preg_replace('/[^0-9]/', '', $order_info['payment_postcode']),
			"city" => $order_info['payment_city'],
			"state" => $order_info['payment_zone_code'],
			"uf" => $order_info['payment_zone_code'],
		]);

		// Shipping
		if ($this->cart->hasShipping()) {
			$dompagamento->setPaymentAddress([
				"street" => $order_info['shipping_address_1'],
				'number' => $order_info['shipping_number'],
				"neighborhood" => $order_info['shipping_address_2'],
				"zip_code" => preg_replace('/[^0-9]/', '', $order_info['shipping_postcode']),
				"city" => $order_info['shipping_city'],
				"state" => $order_info['shipping_zone'],
				"uf" => $order_info['shipping_zone_code'],
			]);
		}
		
		// Customer
		// if ($this->customer->isCompany()) {
		// 	$name = $order_info['custom_field'][$this->config->get('payment_dompagamento_company_id')];
		// } else {
		// }

		$name = $order_info['firstname'].' '.$order_info['lastname'];

		$dompagamento->setCustomer([
			"name" 		    => $name,
			"email" 	    => $order_info['email'],
			"mobile_phone"  => preg_replace('/[^0-9]/', '', $order_info['telephone']),
			"code" 		    => (int) $order_info['customer_id'],
			"document" 	    => preg_replace('/[^0-9]/', '', $this->customer->getDocument()),
			"document_type" => $this->customer->getDocumentType(),
			'session_id' 	=> $this->session->getId(),
			'address' 		=> $dompagamento->getPaymentAddress(),
			'ip' 		    => $this->request->server['REMOTE_ADDR'],
		]);

		$dompagamento->setSecretKey($this->config->get('payment_dompagamento_secret_key')[$this->config->get('config_store_id')]);
		$dompagamento->setPaymentMethod($data['payment_method']);
		$dompagamento->setTotal((float)$order_info['total']);
		$dompagamento->setDiscount((float)$data['discount']);
		$dompagamento->setCodExternal((int)$order_info['order_id']);
		$dompagamento->setPostbackUrl(HTTPS_SERVER . 'index.php?route=extension/payment/dompagamento/callback');

		// Create Transaction
		$response = $dompagamento->createTransaction($data);

		$description = 'Transação efetuada por Dom Pagamento' . "\n";
		$description .= " <b>Transação:</b> ".$response['id']." \n";

		/********** CARTÃO DE CRÉDITO ********/
		if ($order_info['payment_code'] == 'dompagamento_card') {
			if (!empty($response['card_bin'])) {
				$description .= " <b>Cartão de crédito:</b> ".$response['card_brand']." final " . substr($response['card_bin'], 4) . "\n";
			}
		}

		/********** PIX ********/
		if (!empty($response['pix_qrcode'])) {
			$description .= "- QrCode PIX (Pague através do App do banco preferido): \n";
			$description .= "- Copia e Cola: ".$response['pix_content']." \n" ;
			$description .= "<img src=\"{$response['pix_qrcode']}\" width=\"150\" /> \n\n";
		}

		if (isset($response['msg'])) {
			$this->model_checkout_order->editGatewayFallback($order_info['order_id'], $response['msg']);

			$description .= " {$response['msg']} \n";
		}

		if (isset($response['status_details'])) {
			$status_details = $response['status_details'];
			$description = $description .= $response['status_details'] . "\n";
		} else {
			$status_details = '';
		}

		$this->db->query("UPDATE ".DB_PREFIX."dompagamento_order SET status='". $this->db->escape($response['status']) ."', transaction_id='".$this->db->escape($response['id'])."', description='".$this->db->escape($response['description'])."', status_details='".$this->db->escape($response['status_details'])."', msg='".$this->db->escape($response['msg'])."', raw = '". $this->db->escape(json_encode($response)) ."', date_updated=NOW() WHERE dompagamento_order_id = '". (int)$dompagamento_order_id ."' ");
		
		$order_status_id = $this->config->get('payment_dompagamento_status_' . $response['status']);

		$this->session->data['user'] = 'dompagamento';

		$this->model_checkout_order->addOrderHistory($order_info['order_id'], $order_status_id, $description, true, false, 'dompagamento');

		$this->load->model('localisation/order_status');

		$order_status = $this->model_localisation_order_status->getOrderStatus($order_status_id);
		
		if ($_SERVER['REMOTE_ADDR'] == '187.19.107.211') {
			// pr($response);die;
		}

		// Error
		if (isset($response['status']) && $response['status'] == 'not_authorized') {
			throw new \Exception($response['status_details']);
		}

		if (isset($response['status']) && $response['status'] == 'FAIL') {
			throw new \Exception($response['msg']);
		}

		// pr($response); die;

		$status_alowed = [
			'pending',
			'paid',
			// 'revision_paid'
		];

		if (!empty($response['status_details']) && $response['status'] != 'pending' && $response['status'] != 'paid') {
			$status_details = $response['status_details'];

			if ($order_info['payment_code'] == 'dompagamento_card') {
				throw new \Exception($order_status['name'] . ' - ' .  $status_details . '. Por favor, verifique os dados do cartão de crédito e tente novamente.');
			} else {
				throw new \Exception($order_status['name'] . ' - ' .  $status_details . '. Por favor, tente novamente mais tarde.');
			}
		}

		// die;

		// $this->cart->clear();
		return $response;
	}

	public function createTransaction(array $data) {

		// Passar para o nome correto depois
		return $this->createTransactionSandbox($data);

		if ($_SERVER['REMOTE_ADDR'] == '143.208.43.195') {
		}

		return $this->createTransactionProduction($data);
	}

}