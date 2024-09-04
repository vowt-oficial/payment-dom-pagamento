<?php

class ControllerExtensionPaymentDomPagamentoBoleto extends Controller {

    public function index() {
        $data = array();

		unset($this->session->data['dompagamento_captcha']);

		$this->load->model('extension/payment/dompagamento');

		$token = $this->model_extension_payment_dompagamento->getToken('boleto');

        $data['confirm'] = $this->url->link('extension/payment/dompagamento_boleto/confirm&token=' . $token);

        // // Captcha
        if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] != 'sandbox' && isset($this->session->data['dompagamento_captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
            $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
        }

        $data['header'] = $this->load->controller('extension/payment/dompagamento/headerTemplate');
        
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['is_error'] = $order_info['total'] <= 1;

        return $this->load->view('extension/payment/dompagamento_boleto', $data);
    }

    protected function validateForm() {
        if (empty($this->session->data['order_id'])) {
            throw new \Exception('Pedido inválido.');
        }

		// Validate Token
		if (isset($this->request->get['token'])) {
			$token = $this->request->get['token'];
		} else {
            throw new \Exception('Token inválido.');
		}

		$this->load->model('extension/payment/dompagamento');

		$token = $this->model_extension_payment_dompagamento->validateToken('boleto', $token);

        if (empty($this->session->data['payment_method']['code']) || $this->session->data['payment_method']['code'] != 'dompagamento_boleto') {
            throw new \Exception('Forma de pagamento inválido (pagar.me boleto).');
        }

        // Captcha
        if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] != 'sandbox' && isset($this->session->data['dompagamento_captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
            $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

            if ($captcha) {
                throw new \Exception($captcha);
            }
        }
    }

    public function confirm() {
        $json = array();

        try {
			
            $this->validateForm();
            
            $this->load->model('extension/payment/dompagamento_boleto');

            $this->model_extension_payment_dompagamento_boleto->createTransaction();

            $json['success'] = true;
            $json['order_id'] = $this->session->data['order_id'];

        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->session->data['dompagamento_captcha'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));    
    }

}