<?php
/**
 * Cleomar Campos
*/
class ModelExtensionTotalDomPagamentoDiscount extends Model
{

    public function getTotal($total) {

        if (empty($this->session->data['payment_method']['code'])) {
            return;
        }

        $status = true;

        $payment_code = substr($this->session->data['payment_method']['code'], strlen('dompagamento_'));

        if ($this->config->get('total_dompagamento_discount_'.$payment_code.'_value')[$this->config->get('config_store_id')] <= 0) {
            $status = false;
        }

        if ($status) {
            $subtotal = $this->cart->getSubTotal();

            if ($this->config->get('total_dompagamento_discount_'.$payment_code.'_type')[$this->config->get('config_store_id')] == 'p') {
                $discount_total = $subtotal - ($subtotal * $this->config->get('total_dompagamento_discount_'.$payment_code.'_value')[$this->config->get('config_store_id')] / 100);
                $discount_total = ($subtotal - $discount_total);
            } else {
                $discount_total = ((float) $this->config->get('total_dompagamento_discount_'.$payment_code.'_value')[$this->config->get('config_store_id')]);
            }

            $total['totals'][] = array(
                'code'       => 'dompagamento_discount',
                'title'      => 'Desconto ' . $this->session->data['payment_method']['title'],
                'value'      => -$discount_total,
                'sort_order' => $this->config->get('total_dompagamento_discount_sort_order')[$this->config->get('config_store_id')],
            );

            $total['total'] -= $discount_total;
        }
    }
  
}
