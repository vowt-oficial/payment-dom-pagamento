<?php
/*
    Author: Cleomar Campos
    cleomarocampos@gmail.com
*/
class ControllerExtensionPaymentDomPagamentoCard extends Controller {

    public function index() {
        return $this->response->redirect($this->url->link('extension/payment/dompagamento', 'user_token='. $this->session->data['user_token']));
    }

    public function order()
    {
        $this->load->model('extension/payment/dompagamento');

        $data = $this->model_extension_payment_dompagamento->getTransaction($this->request->get['order_id']);

        $data['transaction'] = json_encode($data);

        return $this->load->view('extension/payment/dompagamento_order', $data);
    }

    public function form() {
        if (isset($this->request->post['total_dompagamento_custom_sort_order'])) {
            $data['total_dompagamento_custom_sort_order'] = $this->request->post['total_dompagamento_custom_sort_order'];
        } else {
            $data['total_dompagamento_custom_sort_order'] = $this->config->get('total_dompagamento_custom_sort_order');
        }

        if (isset($this->request->post['total_dompagamento_discount_card_value'])) {
            $data['total_dompagamento_discount_card_value'] = $this->request->post['total_dompagamento_discount_card_value'];
        } else {
            $data['total_dompagamento_discount_card_value'] = $this->config->get('total_dompagamento_discount_card_value') ?: 0;
        }
        
        if (isset($this->request->post['total_dompagamento_discount_card_type'])) {
            $data['total_dompagamento_discount_card_type'] = $this->request->post['total_dompagamento_discount_card_type'];
        } else {
            $data['total_dompagamento_discount_card_type'] = $this->config->get('total_dompagamento_discount_card_type');
        }

        if (isset($this->request->post['payment_dompagamento_card_status'])) {
            $data['payment_dompagamento_card_status'] = $this->request->post['payment_dompagamento_card_status'];
        } else {
            $data['payment_dompagamento_card_status'] = $this->config->get('payment_dompagamento_card_status');
        }

        if (isset($this->request->post['payment_dompagamento_card_title'])) {
            $data['payment_dompagamento_card_title'] = $this->request->post['payment_dompagamento_card_title'];
        } else {
            $data['payment_dompagamento_card_title'] = $this->config->get('payment_dompagamento_card_title') ?: 'Cartão de crédito';
        }

        if (isset($this->request->post['payment_dompagamento_card_max_installments'])) {
            $data['payment_dompagamento_card_max_installments'] = $this->request->post['payment_dompagamento_card_max_installments'];
        } else {
            $data['payment_dompagamento_card_max_installments'] = $this->config->get('payment_dompagamento_card_max_installments');
        }

        if (isset($this->request->post['payment_dompagamento_card_installment'])) {
            $data['payment_dompagamento_card_installment'] = $this->request->post['payment_dompagamento_card_installment'];
        } else {
            $data['payment_dompagamento_card_installment'] = $this->config->get('payment_dompagamento_card_installment');
        }

        foreach (range(1, 12) as $installment) {
            $data['installments'][$installment] = $installment;
        }

        if (isset($this->request->post['payment_dompagamento_card_interest_rate'])) {
            $data['payment_dompagamento_card_interest_rate'] = $this->request->post['payment_dompagamento_card_interest_rate'];
        } else {
            $data['payment_dompagamento_card_interest_rate'] = $this->config->get('payment_dompagamento_card_interest_rate');
        }

        foreach (range(1, 12) as $installment) {
            $data['installments'][$installment] = $installment;
        }

        if (isset($this->request->post['payment_dompagamento_card_try'])) {
            $data['payment_dompagamento_card_try'] = $this->request->post['payment_dompagamento_card_try'];
        } else {
            $data['payment_dompagamento_card_try'] = $this->config->get('payment_dompagamento_card_try') ?? 5;
        }

        if (isset($this->request->post['payment_dompagamento_max_card'])) {
            $data['payment_dompagamento_max_card'] = $this->request->post['payment_dompagamento_max_card'];
        } else {
            $data['payment_dompagamento_max_card'] = $this->config->get('payment_dompagamento_max_card') ?? 5;
        }

        if (isset($this->request->post['payment_dompagamento_card_operation_type'])) {
            $data['payment_dompagamento_card_operation_type'] = $this->request->post['payment_dompagamento_card_operation_type'];
        } else {
            $data['payment_dompagamento_card_operation_type'] = $this->config->get('payment_dompagamento_card_operation_type');
        }

        if (isset($this->request->post['payment_dompagamento_card_min_installment'])) {
            $data['payment_dompagamento_card_min_installment'] = $this->request->post['payment_dompagamento_card_min_installment'];
        } else {
            $data['payment_dompagamento_card_min_installment'] = $this->config->get('payment_dompagamento_card_min_installment');
        }

        if (isset($this->request->post['payment_dompagamento_card_custom_sort_order'])) {
            $data['payment_dompagamento_card_custom_sort_order'] = $this->request->post['payment_dompagamento_card_custom_sort_order'];
        } else {
            $data['payment_dompagamento_card_custom_sort_order'] = $this->config->get('payment_dompagamento_card_custom_sort_order');
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

        return $this->load->view('extension/payment/dompagamento_card', $data);
    }

}