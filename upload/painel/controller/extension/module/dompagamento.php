<?php
class ControllerExtensionModuleDomPagamento extends Controller {
	public function index($setting = []) {
		unset($this->session->data['dompagamento_captcha']);
		unset($this->session->data['dompagamento_attemp']);

		$this->load->language('account/account');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if (!$order_info) {
			return;
		}

		$this->load->model('extension/payment/dompagamento');

		$transaction_info = $this->model_extension_payment_dompagamento->getTransactionByOrderId($order_id);

		if (!$transaction_info) {
			return;
		}

		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];
		$data['transaction_id'] = $transaction_info['transaction_id'];
		$data['order_id'] = $order_info['order_id'];
		$data['postcode'] = $order_info['payment_postcode'];
		$data['address_1'] = $order_info['payment_address_1'];
		$data['address_2'] = $order_info['payment_address_2'];
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code']);
		$data['date'] = date('d/m/Y', strtotime($order_info['date_added']));

		// Status
		$this->load->model('localisation/order_status');

		$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);
		
		$data['order_status_name'] = $order_status_info['name'];
		$data['order_status'] = $transaction_info['status'];

		if ($data['order_status'] == 'paid') {
			$data['alert'] = 'success';
		} else if ($data['order_status'] == 'canceled') {
			$data['alert'] = 'danger';
		} else {
			$data['alert'] = 'info';
		}

		$data['account'] = $this->url->link('account/account');
		$data['order'] = $this->url->link('account/order', 'order_id='. $order_id);

		switch ($transaction_info['payment_code']) {
			case 'dompagamento_boleto':
				$data['boleto_url'] = $transaction_info['raw']['boleto_url'];
				$data['boleto_digitable_line'] = $transaction_info['raw']['boleto_digitable_line'];

				$data['payment'] = $this->load->view('extension/module/dompagamento_boleto_info', $data);
			break;
			case 'dompagamento_pix':
				$data['pix_qrcode'] = $transaction_info['raw']['pix_qrcode'];
				$data['pix_content'] = $transaction_info['raw']['pix_content'];
				$data['pix_expire'] = $transaction_info['raw']['pix_expire'];

				$data['payment'] = $this->load->view('extension/module/dompagamento_pix_info', $data);
			break;
			case 'dompagamento_card':
				$data['payment'] = $this->load->view('extension/module/dompagamento_card_info', $data);
			break;
			default:
				// return;
			break;
		}

		return $this->load->view('extension/module/dompagamento', $data);
	}
}