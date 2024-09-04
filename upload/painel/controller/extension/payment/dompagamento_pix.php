<?php

class ControllerExtensionPaymentDomPagamentoPix extends Controller {

    public function index() {
        return $this->response->redirect($this->url->link('extension/payment/dompagamento', 'user_token='. $this->session->data['user_token']));
    }
    
    public function form() {

        if (isset($this->request->post['total_dompagamento_discount_pix_value'])) {
            $data['total_dompagamento_discount_pix_value'] = $this->request->post['total_dompagamento_discount_pix_value'];
        } else {
            $data['total_dompagamento_discount_pix_value'] = $this->config->get('total_dompagamento_discount_pix_value') ?: 0;
        }
        
        if (isset($this->request->post['total_dompagamento_discount_pix_type'])) {
            $data['total_dompagamento_discount_pix_type'] = $this->request->post['total_dompagamento_discount_pix_type'];
        } else {
            $data['total_dompagamento_discount_pix_type'] = $this->config->get('total_dompagamento_discount_pix_type');
        }

        if (isset($this->request->post['payment_dompagamento_pix_title'])) {
            $data['payment_dompagamento_pix_title'] = $this->request->post['payment_dompagamento_pix_title'];
        } else {
            $data['payment_dompagamento_pix_title'] = $this->config->get('payment_dompagamento_pix_title') ?: 'Pagar com PIX';
        }

        if (isset($this->request->post['payment_dompagamento_pix_days'])) {
            $data['payment_dompagamento_pix_days'] = $this->request->post['payment_dompagamento_pix_days'];
        } else {
            $data['payment_dompagamento_pix_days'] = $this->config->get('payment_dompagamento_pix_days') ?: 1;
        }

        if (isset($this->request->post['payment_dompagamento_pix_status'])) {
            $data['payment_dompagamento_pix_status'] = $this->request->post['payment_dompagamento_pix_status'];
        } else {
            $data['payment_dompagamento_pix_status'] = $this->config->get('payment_dompagamento_pix_status');
        }

        if (isset($this->request->post['payment_dompagamento_pix_order_state_id'])) {
            $data['payment_dompagamento_pix_order_state_id'] = $this->request->post['payment_dompagamento_pix_order_state_id'];
        } else {
            $data['payment_dompagamento_pix_order_state_id'] = $this->config->get('payment_dompagamento_pix_order_state_id');
        }

        if (isset($this->request->post['payment_dompagamento_pix_custom_sort_order'])) {
            $data['payment_dompagamento_pix_custom_sort_order'] = $this->request->post['payment_dompagamento_pix_custom_sort_order'];
        } else {
            $data['payment_dompagamento_pix_custom_sort_order'] = $this->config->get('payment_dompagamento_pix_custom_sort_order');
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

        return $this->load->view('extension/payment/dompagamento_pix', $data);
    }
}