<?php

class ControllerExtensionPaymentDompagamentoCard extends Controller {

    public function index() {
		try {

			// Captcha
			if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] != 'sandbox' && isset($this->session->data['dompagamento_captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
			}

			$this->load->model('checkout/order');

			if (!empty($this->session->data['order_id'])) {
				$order_id = $this->session->data['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if (!$order_info) {
				throw new \Exception("Pedido inválido");
			}

			$data['installments'] = [];

			foreach (range(1, $this->config->get('payment_dompagamento_card_max_installments')[$this->config->get('config_store_id')]) as $installment) {
				if (!empty($this->config->get('payment_dompagamento_card_installment')[$this->config->get('config_store_id')][$installment])) {
					$interest = (float) $this->config->get('payment_dompagamento_card_installment')[$this->config->get('config_store_id')][$installment];
				} else {
					$interest = 0;
				}

				$value_installment = $order_info['total'] / $installment;
				$fee = $value_installment * ( $interest / 100 );
				$total_interest = $value_installment + $fee;

				// Validamos a parcela minima
				if ($total_interest <= $this->config->get('payment_dompagamento_card_min_installment')[$this->config->get('config_store_id')]) {
					continue;
				}

				$data['installments'][] = [
					'installment' => $installment,
					'interest' => $interest,
					'value_interest' => $this->currency->format($total_interest, $order_info['currency_code']),
					'total' => $this->currency->format($total_interest * $installment, $order_info['currency_code']),
				];
			}
			
			$data['is_test'] = $this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] == 'sandbox';
			$data['public_key'] = $this->config->get('payment_dompagamento_public_key')[$this->config->get('config_store_id')];

			$this->load->model('extension/payment/dompagamento');

			$token = $this->model_extension_payment_dompagamento->getToken('card');
			
			$data['confirm'] = $this->url->link('extension/payment/dompagamento_card/confirm&token=' . $token);

			$data['header'] = $this->load->controller('extension/payment/dompagamento/headerTemplate');

			return $this->load->view('extension/payment/dompagamento_card', $data);

		} catch (\Exception $e) {
			$this->log->write(print_r([ 'Dom Pagamento' => $e->getMessage() ], true));
			//throw $th;
			return;
		}
    }

    public function confirm() {
        $json = array();

        try {

            $post = $this->validateForm();

            $this->load->model('extension/payment/dompagamento_card');

            $response = $this->model_extension_payment_dompagamento_card->createTransaction($post);

            $json['success'] = true;
            $json['status'] = $response['status'];
            $json['order_id'] = $this->session->data['order_id'];

        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->session->data['dompagamento_captcha'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validateForm() {
		// Validate Token
		if (isset($this->request->get['token'])) {
			$token = $this->request->get['token'];
		} else {
			throw new \Exception('Token inválido.');
		}

		$this->load->model('extension/payment/dompagamento');

		$token = $this->model_extension_payment_dompagamento->validateToken('card', $token);

		if (empty($this->session->data['payment_method']['code']) || $this->session->data['payment_method']['code'] != 'dompagamento_card') {
			throw new \Exception('Forma de pagamento inválido (pagar.me card).');
		}
		
        if ($this->session->data['payment_method']['code'] != 'dompagamento_card') {
            throw new \Exception('Forma de pagamento inválido');
        }

        // Validamos se os dados estão todos corretos
        $this->load->model('extension/payment/dompagamento');

        $this->model_extension_payment_dompagamento->validateOrder();

        $this->load->model('extension/payment/dompagamento_card');

        if (empty($this->request->post['token'])) {
            throw new \Exception('Ops, a ( token ) não é um valor válido ');
        }

        $installments = $this->model_extension_payment_dompagamento_card->getInstallments($this->session->data['order_id']);

        if (!isset($installments[ $this->request->post['installments'] - 1 ])) {
            throw new \Exception('Verifique se você selecionou a parcela corretamente');
        }

        // Captcha
        if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] != 'sandbox' && isset($this->session->data['dompagamento_captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
            $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

            if ($captcha) {
                throw new \Exception($captcha);
            }
        }
		
		return $this->request->post;
    }

}
