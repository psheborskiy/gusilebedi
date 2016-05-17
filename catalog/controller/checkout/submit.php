<?php
class Controllercheckoutsubmit extends Controller {
	public function index() {

		/*************** ЦЕНА  ***************************/
		$order_data['totals'] = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();

		$this->load->model('extension/extension');

		$sort_order = array();

		$results = $this->model_extension_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$this->{'model_total_' . $result['code']}->getTotal($order_data['totals'], $total, $taxes);
			}
		}

		$sort_order = array();

		foreach ($order_data['totals'] as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $order_data['totals']);
		/*************** ЦЕНА  ***************************/



		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');
		$order_data['shipping_firstname'] = '1';
		$order_data['shipping_lastname'] = '2';
		$order_data['shipping_company'] = '';
		$order_data['store_url'] = HTTP_SERVER;
		$order_data['customer_group_id'] =1;
		$order_data['firstname'] ="Юрій";
		$order_data['lastname'] = "Psheborsky";
		$order_data['email'] = "vinity.com@mail.ru";
		$order_data['telephone'] = "0987655665";
		$order_data['fax'] = "0";
		$order_data['custom_field'] = "Замовлення";
		$order_data['customer_id'] = 0;
		$order_data['shipping_address_1'] = '4';
		$order_data['shipping_address_2'] = '5';
		$order_data['shipping_city'] = '6';
		$order_data['shipping_postcode'] = '7';
		$order_data['shipping_zone'] = '8';
		$order_data['shipping_zone_id'] = '9';
		$order_data['shipping_country'] = '11';
		$order_data['shipping_country_id'] = '12';
		$order_data['shipping_address_format'] = '13';
		$order_data['shipping_custom_field'] = array();
		$order_data['shipping_method'] = '14';
		$order_data['shipping_code'] = '15';

		$order_data['products'] = array();
		$order_data['vouchers'] = array();


		$order_data['payment_firstname'] = "Пеймент Вася";
		$order_data['payment_lastname'] = "Пеймент фамилия";
		$order_data['payment_company'] = "1";
		$order_data['payment_address_1'] = "Адрес";
		$order_data['payment_address_2'] = "2";
		$order_data['payment_city'] = "Город";
		$order_data['payment_postcode'] = "Код";
		$order_data['payment_zone'] = "Область";
		$order_data['payment_zone_id'] = "1";
		$order_data['payment_country'] = "Украина";
		$order_data['payment_country_id'] = "1";
		$order_data['payment_address_format'] = "";
		$order_data['payment_custom_field'] =  array();
		$order_data['payment_method'] = 'Готівка';
		$order_data['payment_code'] = '';
		$order_data['comment'] = "Коментарий";
		$order_data['total'] = $total;
		$order_data['affiliate_id'] = 0;
		$order_data['commission'] = 0;
		$order_data['marketing_id'] = 0;
		$order_data['tracking'] = '';

		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			$order_data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			);
		}

		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId();
		$order_data['currency_code'] = $this->currency->getCode();
		$order_data['currency_value'] = $this->currency->getValue($this->currency->getCode());
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}

		$this->load->model('checkout/order');

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			// Add to activity log
			$this->load->model('account/activity');


			$activity_data = array(
				'name'     => "Юра",
				'order_id' => $this->session->data['order_id']
			);

			$this->model_account_activity->addActivity('order_guest', $activity_data);
			$this->load->model('checkout/order');
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('cod_order_status_id'));

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);

		}

	}

}