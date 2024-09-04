<?php

class ControllerExtensionPaymentDomPagamentoPix extends Controller {

    public function index() {
        $data = array();

		// unset($this->session->data['dompagamento_captcha']);

		$this->load->model('extension/payment/dompagamento');

		$token = $this->model_extension_payment_dompagamento->getToken('pix');

        $data['confirm'] = $this->url->link('extension/payment/dompagamento_pix/confirm&token=' . $token);

        $data['header'] = $this->load->controller('extension/payment/dompagamento/headerTemplate');

        // Captcha
        if ($this->config->get('payment_dompagamento_store_env')[$this->config->get('config_store_id')] != 'sandbox' && isset($this->session->data['dompagamento_captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
            $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['is_error'] = $order_info['total'] <= 1;

        return $this->load->view('extension/payment/dompagamento_pix', $data);
    }

    protected function validadeForm() {

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

		$token = $this->model_extension_payment_dompagamento->validateToken('pix', $token);

		if (empty($this->session->data['payment_method']['code']) || $this->session->data['payment_method']['code'] != 'dompagamento_pix') {
			throw new \Exception('Forma de pagamento inválido (dompagamento pix).');
		}
		
        if ($this->session->data['payment_method']['code'] != 'dompagamento_pix') {
            throw new \Exception('Forma de pagamento inválido.');
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

            $this->validadeForm();

            $this->load->model('extension/payment/dompagamento_pix');

            $this->model_extension_payment_dompagamento_pix->createTransaction();

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