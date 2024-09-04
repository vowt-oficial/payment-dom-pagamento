<?php
/*
    Author: Cleomar Campos
    cleomarocampos@gmail.com
*/
class ControllerExtensionPaymentDomPagamento extends Controller
{
    private $error = array();

    public function index() {
        $this->document->setTitle('dompagamento');

        $this->load->language('extension/payment/dompagamento');
        $this->load->model('setting/setting');

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/dompagamento', 'user_token=' . $this->session->data['user_token'], true)
		);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $data['payment_dompagamento_card_sort_order'] = 1;
            $data['payment_dompagamento_boleto_sort_order'] = 2;
            $data['payment_dompagamento_pix_sort_order'] = 3;

            if ($this->request->post['total_dompagamento_discount_card_value'] > 0 || $this->request->post['total_dompagamento_discount_boleto_value'] > 0 || $this->request->post['total_dompagamento_discount_pix_value'] > 0) {
				$this->request->post['total_dompagamento_discount_status'] = true;
			} else {
				$this->request->post['total_dompagamento_discount_status'] = false;
			}

            $this->model_setting_setting->editSetting('total_dompagamento_discount', $this->request->post);
            $this->model_setting_setting->editSetting('payment_dompagamento', $this->request->post);
            
            $this->session->data['success'] = 'Sucesso';

            $this->response->redirect($this->url->link( 'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        /*************************************/
        /****** INFORMAÇÕES GENERICAS *******/
        if (isset($this->request->post['payment_dompagamento_limit'])) {
            $data['payment_dompagamento_limit'] = $this->request->post['payment_dompagamento_limit'];
        } else {
            $data['payment_dompagamento_limit'] = $this->config->get('payment_dompagamento_limit') ?? 3;
        }

        /*************************************/
        /****** MULTI MEIO DE PAGAMENTO *******/
        if (isset($this->request->post['payment_dompagamento_time'])) {
            $data['payment_dompagamento_time'] = $this->request->post['payment_dompagamento_time'];
        } else {
            $data['payment_dompagamento_time'] = $this->config->get('payment_dompagamento_time') ?? 2;
        }

        if (isset($this->request->post['payment_dompagamento_status'])) {
            $data['payment_dompagamento_status'] = $this->request->post['payment_dompagamento_status'];
        } else {
            $data['payment_dompagamento_status'] = $this->config->get('payment_dompagamento_status');
        }

        if (isset($this->request->post['payment_dompagamento_store_status'])) {
            $data['payment_dompagamento_store_status'] = $this->request->post['payment_dompagamento_store_status'];
        } else {
            $data['payment_dompagamento_store_status'] = $this->config->get('payment_dompagamento_store_status');
        }

        if (isset($this->request->post['payment_dompagamento_sort_order'])) {
            $data['payment_dompagamento_sort_order'] = $this->request->post['payment_dompagamento_sort_order'];
        } else {
            $data['payment_dompagamento_sort_order'] = $this->config->get('payment_dompagamento_sort_order');
        }

        if (isset($this->request->post['total_dompagamento_discount_custom_sort_order'])) {
            $data['total_dompagamento_discount_custom_sort_order'] = $this->request->post['total_dompagamento_discount_custom_sort_order'];
        } else {
            $data['total_dompagamento_discount_custom_sort_order'] = $this->config->get('total_dompagamento_discount_custom_sort_order');
        }

		if (isset($this->request->post['payment_dompagamento_store_env'])) {
            $data['payment_dompagamento_store_env'] = $this->request->post['payment_dompagamento_store_env'];
        } else {
            $data['payment_dompagamento_store_env'] = $this->config->get('payment_dompagamento_store_env');
        }

		if (isset($this->request->post['payment_dompagamento_public_key'])) {
            $data['payment_dompagamento_public_key'] = $this->request->post['payment_dompagamento_public_key'];
        } else {
            $data['payment_dompagamento_public_key'] = $this->config->get('payment_dompagamento_public_key');
        }

        if (isset($this->request->post['payment_dompagamento_secret_key'])) {
            $data['payment_dompagamento_secret_key'] = $this->request->post['payment_dompagamento_secret_key'];
        } else {
            $data['payment_dompagamento_secret_key'] = $this->config->get('payment_dompagamento_secret_key');
        }

		$data['captcha'] = $this->config->get('config_captcha');

        $data['config'] = $this->url->link('setting/setting&user_token=' . $this->session->data['user_token']);

        $this->load->model('customer/custom_field');

        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();

        /*************************************/
        /************** STATUS ***************/
        $this->load->model('localisation/order_status');

        $data['statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['status'] = [
            [ 'value' => $this->config->get('payment_dompagamento_status_pending'), 'name' => 'payment_dompagamento_status_pending', 'label' => 'Pendente' ],
            [ 'value' => $this->config->get('payment_dompagamento_status_paid'), 'name' => 'payment_dompagamento_status_paid', 'label' => 'Pago' ],
            [ 'value' => $this->config->get('payment_dompagamento_canceled'), 'name' => 'payment_dompagamento_canceled', 'label' => 'Cancelado' ],
            [ 'value' => $this->config->get('payment_dompagamento_status_processing'), 'name' => 'payment_dompagamento_status_processing', 'label' => 'Processando' ],
            [ 'value' => $this->config->get('payment_dompagamento_status_failed'), 'name' => 'payment_dompagamento_status_failed', 'label' => 'Falha' ],
            [ 'value' => $this->config->get('payment_dompagamento_status_not_authorized'), 'name' => 'payment_dompagamento_status_not_authorized', 'label' => 'Não autorizado' ],
            [ 'value' => $this->config->get('payment_dompagamento_status_chargedback'), 'name' => 'payment_dompagamento_status_chargedback', 'label' => 'Chargedback' ],
        ];

        $this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default'),
            'cron' => "curl --silent " . HTTPS_CATALOG . 'index.php?route=extension/payment/dompagamento/cron&token=' . @$data['payment_dompagamento_app_token'] . " > /dev/null ",
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name'],
                'cron' => "curl --silent " . $store['ssl'] . 'index.php?route=extension/payment/dompagamento/cron&token=' . @$data['payment_dompagamento_app_token']. " > /dev/null ",
			);
		}

		/*****************************************/
        /*** FORMAS DE PAGAMENTOS DISPONIVEIS ***/
        $data['card'] = $this->load->controller('extension/payment/dompagamento_card/form');
        $data['pix'] = $this->load->controller('extension/payment/dompagamento_pix/form');
        $data['boleto'] = $this->load->controller('extension/payment/dompagamento_boleto/form');

        $data['callback'] = html_entity_decode($this->url->link('extension/payment/dompagamento/callback', 'user_token=' . $this->session->data['user_token']));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/dompagamento', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/dompagamento')) {
            $this->error['warning'] = 'Você não tem permissões suficientes para isso';
        }

        return !$this->error;
    }

    public function editSetting($code, $data, $store_id = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
				}
			}
		}
	}

    public function getFieldId($code) {
        $escapedCodes = array_map(array($this->db, 'escape'), $code);
        $implodedCodes = implode("', '", $escapedCodes);

        $fieldExistsQuery = $this->db->query("SHOW COLUMNS FROM ".DB_PREFIX."custom_field LIKE 'code'");

        if ($fieldExistsQuery->num_rows > 0) {
            // O campo 'code' existe, então você pode usar a consulta original
            $query = $this->db->query("SELECT * FROM ".DB_PREFIX."custom_field WHERE code IN ('".$implodedCodes."') ORDER BY code");
        } else { 
            $query = $this->db->query("SELECT * FROM ".DB_PREFIX."custom_field_description WHERE name IN ('".$implodedCodes."') AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY name");
        }

        return $query->row['custom_field_id'];
    }

    public function getStatusId($code) {
        $escapedCodes = array_map(array($this->db, 'escape'), $code);
        $implodedCodes = implode("', '", $escapedCodes);

        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."order_status WHERE name IN ('".$implodedCodes."') AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY name");

        return $query->row['order_status_id'];
    }

    public function install() {
		$this->load->model('setting/event');
        $this->model_setting_event->addEvent('dompagamento_init_checkout_4', 'catalog/controller/extension/module/checkoutpro/header/before', 'extension/payment/dompagamento/initialize');

        $this->load->model('setting/setting');

        // Custom Field
        $data['payment_dompagamento_limit'] = 3;

        // Order Status
        $data['payment_dompagamento_status_pending'] = $this->getStatusId(['Aguardando pagamento']);
        $data['payment_dompagamento_status_paid'] = $this->getStatusId(['Pagamento aprovado']);
        $data['payment_dompagamento_canceled'] = $this->getStatusId(['Cancelado']);
        $data['payment_dompagamento_status_processing'] = $this->getStatusId(['Processando']);
        $data['payment_dompagamento_status_failed'] = $this->getStatusId(['Falhou']);
        $data['payment_dompagamento_status_chargedback'] = $this->getStatusId(['Chargeback']);

        // Card
        $data['payment_dompagamento_card_title'][0] = 'Cartão de Crédito';
        $data['payment_dompagamento_card_max_installments'][0] = 6;
        $data['payment_dompagamento_card_min_installment'][0] = 1;
        $data['payment_dompagamento_card_operation_type'][0] = 'auth_and_capture';
        $data['payment_dompagamento_card_custom_sort_order'][0] = 0;
        $data['payment_dompagamento_card_sort_order'] = 1;
        $data['payment_dompagamento_card_status'][0] = 1;

        for ($i = 1; $i <= 12; $i++) {
            $data['payment_dompagamento_card_installment'][0][$i] = 0;
        }

        // Card total
        $data['total_dompagamento_discount_card_type'][0] = 'p';
        $data['total_dompagamento_discount_card_value'][0] = 0;

        // Boleto
        $data['payment_dompagamento_boleto_title'][0] = 'Boleto Bancário';
        $data['payment_dompagamento_boleto_expire'][0] = 1;
        $data['payment_dompagamento_boleto_custom_sort_order'][0] = 1;
        $data['payment_dompagamento_boleto_sort_order'] = 2;
        $data['payment_dompagamento_boleto_status'][0] = 1;

        // Boleto Total
        $data['total_dompagamento_discount_boleto_value'][0] = 0;
        $data['total_dompagamento_discount_boleto_type'][0] = 'p';

        // PIX
        $data['payment_dompagamento_pix_title'][0] = 'Pagar com PIX';
        $data['payment_dompagamento_pix_days'][0] = 1;
        $data['payment_dompagamento_pix_custom_sort_order'][0] = 2;
        $data['payment_dompagamento_pix_sort_order'] = 3;
        $data['payment_dompagamento_pix_status'][0] = 1;

        // Pix Total
        $data['total_dompagamento_discount_pix_value'][0] = 0;
        $data['total_dompagamento_discount_pix_type'][0] = 'p';
        
        // Total
        $data['payment_dompagamento_status'] = 1;
        $data['payment_dompagamento_store_status'][0] = 1;
        $data['total_dompagamento_discount_custom_sort_order'][0] = 1;

        $data['payment_dompagamento_access_token'] = '';
        $data['payment_dompagamento_account_id'] = '';
        $data['payment_dompagamento_account_public_key'] = '';
        $data['payment_dompagamento_install_id'] = '';
        $data['payment_dompagamento_merchant_id'] = '';

        $data['payment_dompagamento_app_token'] = md5(uniqid());

        $this->model_setting_setting->editSetting('total_dompagamento_discount', $data);
        $this->model_setting_setting->editSetting('payment_dompagamento', $data);

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dompagamento_attempt` (
            `dompagamento_attempt_id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `payment_code` varchar(255) NOT NULL,
            `payment_method` varchar(255) NOT NULL,
            `ip` varchar(70) NOT NULL,
            `card_number` int(11) NOT NULL,
            `status` varchar(25) DEFAULT 'error',
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`dompagamento_attempt_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");


        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dompagamento_order` (
            `dompagamento_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `store_id` int(11) DEFAULT 0,
            `customer_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `transaction_id` varchar(100) DEFAULT NULL,
            `code` varchar(100) NOT NULL,
            `payment_code` varchar(70) NOT NULL,
            `raw` text NOT NULL,
            `env` enum('sandbox','production') NOT NULL DEFAULT 'sandbox',
            `status` varchar(255) DEFAULT 'pending',
            `additional_data` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL,
            `date_updated` datetime NOT NULL,
            PRIMARY KEY (`dompagamento_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
        ");

        $this->load->model('setting/extension');

        $this->model_setting_extension->install('payment', 'dompagamento_boleto');
        $this->model_setting_extension->install('payment', 'dompagamento_pix');
        $this->model_setting_extension->install('payment', 'dompagamento_card');
        $this->model_setting_extension->install('total',   'dompagamento_discount');

        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_boleto');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_boleto');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_pix');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_pix');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_card');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_card');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/total/dompagamento');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/total/dompagamento');
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dompagamento_order`;");

        $this->load->model('setting/extension');

        $this->model_setting_extension->uninstall('payment', 'dompagamento_boleto');
        $this->model_setting_extension->uninstall('payment', 'dompagamento_card');
        $this->model_setting_extension->uninstall('payment', 'dompagamento_pix');
        $this->model_setting_extension->uninstall('total',   'dompagamento_discount');

        $this->load->model('user/user_group');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_boleto');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_boleto');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_pix');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_pix');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/payment/dompagamento_card');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/payment/dompagamento_card');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/total/dompagamento');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/total/dompagamento');
    }

}