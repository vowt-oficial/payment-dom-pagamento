<?php
/*
    Author: Cleomar Campos
    cleomarocampos@gmail.com
*/
class ControllerExtensionPaymentDomPagamentoBoleto extends Controller {

    public function index() {
        return $this->response->redirect($this->url->link('extension/payment/dompagamento', 'user_token='. $this->session->data['user_token']));
    }

    public function form() {
        if (isset($this->request->post['total_dompagamento_discount_boleto_value'])) {
            $data['total_dompagamento_discount_boleto_value'] = $this->request->post['total_dompagamento_discount_boleto_value'];
        } else {
            $data['total_dompagamento_discount_boleto_value'] = $this->config->get('total_dompagamento_discount_boleto_value') ?: 0;
        }
        
        if (isset($this->request->post['total_dompagamento_discount_boleto_type'])) {
            $data['total_dompagamento_discount_boleto_type'] = $this->request->post['total_dompagamento_discount_boleto_type'];
        } else {
            $data['total_dompagamento_discount_boleto_type'] = $this->config->get('total_dompagamento_discount_boleto_type');
        }

        if (isset($this->request->post['payment_dompagamento_boleto_title'])) {
            $data['payment_dompagamento_boleto_title'] = $this->request->post['payment_dompagamento_boleto_title'];
        } else {
            $data['payment_dompagamento_boleto_title'] = $this->config->get('payment_dompagamento_boleto_title') ?: 'Boleto Bancário';
        }

        if (isset($this->request->post['payment_dompagamento_boleto_expire'])) {
            $data['payment_dompagamento_boleto_expire'] = $this->request->post['payment_dompagamento_boleto_expire'];
        } else {
            $data['payment_dompagamento_boleto_expire'] = $this->config->get('payment_dompagamento_boleto_expire') ?: 1;
        }

        if (isset($this->request->post['payment_dompagamento_boleto_custom_sort_order'])) {
            $data['payment_dompagamento_boleto_custom_sort_order'] = $this->request->post['payment_dompagamento_boleto_custom_sort_order'];
        } else {
            $data['payment_dompagamento_boleto_custom_sort_order'] = $this->config->get('payment_dompagamento_boleto_custom_sort_order');
        }

        if (isset($this->request->post['payment_dompagamento_boleto_status'])) {
            $data['payment_dompagamento_boleto_status'] = $this->request->post['payment_dompagamento_boleto_status'];
        } else {
            $data['payment_dompagamento_boleto_status'] = $this->config->get('payment_dompagamento_boleto_status');
        }

        $this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

        return $this->load->view('extension/payment/dompagamento_boleto', $data);
    }

}