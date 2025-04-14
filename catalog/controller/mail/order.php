<?php
include "./dompdf/autoload.inc.php";
require_once('./fpdi/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

class ControllerMailOrder extends Controller {
	public function index(&$route, &$args) {
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}
		
		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
						
		// We need to grab the old order status ID
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
			// If order status is 0 then becomes greater than 0 send main html email
			if (!$order_info['order_status_id'] && $order_status_id) {
				$this->add($order_info, $order_status_id, $comment, $notify);
			} 
			
			// If order status is not 0 then send update text email
			if ($order_info['order_status_id'] && $order_status_id && $notify) {
				$this->edit($order_info, $order_status_id, $comment, $notify);
			}		
		}
	}
		
	public function getOrderStatusID($order_status_id, $customer_group_id) {
		if($customer_group_id == CUSTOMER_GROUP_ID)
		 return ORDER_STATUS_ID;
		
		return $order_status_id; 
	}

	public function sendMailForOrderIs7day($order_info, $order_status_id, $comment, $notify) {
		$this->load->model('account/customer');

		// get customer group id
		$customer_id = $order_info['customer_id'];
		//$customer_group_id = $this->model_account_customer->getGroupFromCustomer($customer_id);

		$order_id = $order_info['order_id'];

		// if is customer group special
		/*if($order_status_id != $this->getOrderStatusID($order_status_id, $customer_group_id)) {
			$order_status_id = $this->getOrderStatusID($order_status_id, $customer_group_id);

			$this->model_checkout_order->editStatusOrder($order_id, $order_status_id);
		}*/
		
		// Check for any downloadable products
		$download_status = false;

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
		
		foreach ($order_products as $order_product) {
			// Check if there are any linked downloads
			$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			if ($product_download_query->row['total']) {
				$download_status = true;
			}
		}
		
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['customer_id'] = $order_info['customer_id'];
		$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

		if ($download_status) {
			$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
		} else {
			$data['download'] = '';
		}

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = ($order_status_id != 17) ? $order_info['payment_method'] : 'vor Ort';
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($comment && $notify) {
			$data['comment'] = nl2br($comment);
		} else {
			$data['comment'] = '';
		}

		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$option_data = array();

			$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

			foreach ($order_options as $order_option) {
				if ($order_option['type'] != 'file') {
					$value = $order_option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $order_option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$data['products'][] = array(
				'name'     => $order_product['name'],
				'model'    => $order_product['model'],
				'option'   => $option_data,
				'quantity' => $order_product['quantity'],
				'price'    => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		// Vouchers
		$data['vouchers'] = array();

		$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_info['order_id']);

		foreach ($order_vouchers as $order_voucher) {
			$data['vouchers'][] = array(
				'description' => $order_voucher['description'],
				'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		// Order Totals
		$data['totals'] = array();
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);

		$this->document->displayOrder($order_totals, 0, 0, 0, 0, 0);
		
		// Add net price
		$count = count($order_totals);
		// if not coupon
		if($count == 4) {
			//print_r($order_totals); die();
			$order_totals[$count - 1] = $order_totals[$count];
		
			$order_totals[$count - 1]['title'] = 'Gesamtnetto';
			$order_totals[$count - 1]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;		
		} else { // if coupon
			//print_r($order_totals); die();
			// Because shipping = 0, so need to reduce total
			//$order_totals[$count - 1]['value'] = $order_totals[$count - 1]['value'] - $order_totals[1]['value'];

			// set shipping is 0
			//$order_totals[1]['value'] = 0;

			$order_totals[$count - 2]['title'] = 'Gesamtnetto';
			$order_totals[$count - 2]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;
			
			//print_r($order_totals); die();
		}
		
		usort($order_totals, function($a, $b) {
			return $a['sort_order'] - $b['sort_order']; // Ascending order
		});
		
		// END
		
		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
	
		$this->load->model('setting/setting');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
		
		if (!$from) {
			$from = $this->config->get('config_email');
		}

		$data['mail_header'] = MAILHEADER;
		$data['mail_footer'] = FOOTER;
		$data['pdf_address'] = PDF_ADDRESS;
		$data['ACCOUNT'] = ACCOUNT;
		$data['text_inform_order'] = $language->get('text_inform_order');
		$data['order_id'] = $order_info['order_id'];
		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];
		$data['total'] = number_format($order_total['value'], 2, ',', '.');
		
		$subject = SUBJECT_CRONTAB1; //html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
		$fromName = html_entity_decode('svapo.de, '.$order_info['store_name'], ENT_QUOTES, 'UTF-8');

		$message = $this->load->view('mail/order_notice_customer_7_days', $data);

		//create pdf
		$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		
		$pdf_name = 'Auftragsbestaetigung-svapo-'.$order_info['order_id'].'.pdf';
		$dompdf->loadHtml($this->load->view('mail/order_pdf', $data));
		$file_location = "./admin/auftrag/".$pdf_name;
		$dompdf->setPaper('A4', 'Horizontal');
		$dompdf->render();
		$pdf = $dompdf->output();
		file_put_contents($file_location, $pdf);

		//echo 'nam';

		$this->document->sendMailSMTP($order_info['email'], $subject, SMTP_USER, $fromName, $message, 'add', $pdf_name);
		
		//$this->sendMail("vu@pixeldusche.com", $subject, SMTP_USER, $fromName, $message, 'add', $pdf_name, $order_status_id, $data);
	}

	public function sendMailForOrderIs14day($order_info, $order_status_id, $comment, $notify) {
		$this->load->model('account/customer');

		// get customer group id
		$customer_id = $order_info['customer_id'];
		//$customer_group_id = $this->model_account_customer->getGroupFromCustomer($customer_id);

		$order_id = $order_info['order_id'];

		// if is customer group special
		/*if($order_status_id != $this->getOrderStatusID($order_status_id, $customer_group_id)) {
			$order_status_id = $this->getOrderStatusID($order_status_id, $customer_group_id);

			$this->model_checkout_order->editStatusOrder($order_id, $order_status_id);
		}*/
		
		// Check for any downloadable products
		$download_status = false;

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
		
		foreach ($order_products as $order_product) {
			// Check if there are any linked downloads
			$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			if ($product_download_query->row['total']) {
				$download_status = true;
			}
		}
		
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['customer_id'] = $order_info['customer_id'];
		$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

		if ($download_status) {
			$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
		} else {
			$data['download'] = '';
		}

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = ($order_status_id != 17) ? $order_info['payment_method'] : 'vor Ort';
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($comment && $notify) {
			$data['comment'] = nl2br($comment);
		} else {
			$data['comment'] = '';
		}

		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$option_data = array();

			$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

			foreach ($order_options as $order_option) {
				if ($order_option['type'] != 'file') {
					$value = $order_option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $order_option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$data['products'][] = array(
				'name'     => $order_product['name'],
				'model'    => $order_product['model'],
				'option'   => $option_data,
				'quantity' => $order_product['quantity'],
				'price'    => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		// Vouchers
		$data['vouchers'] = array();

		$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_info['order_id']);

		foreach ($order_vouchers as $order_voucher) {
			$data['vouchers'][] = array(
				'description' => $order_voucher['description'],
				'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		// Order Totals
		$data['totals'] = array();
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);

		$this->document->displayOrder($order_totals, 0, 0, 0, 0, 0);
		
		// Add net price
		$count = count($order_totals);
		// if not coupon
		if($count == 4) {
			//print_r($order_totals); die();
			$order_totals[$count - 1] = $order_totals[$count];
		
			$order_totals[$count - 1]['title'] = 'Gesamtnetto';
			$order_totals[$count - 1]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;		
		} else { // if coupon
			//print_r($order_totals); die();
			// Because shipping = 0, so need to reduce total
			//$order_totals[$count - 1]['value'] = $order_totals[$count - 1]['value'] - $order_totals[1]['value'];

			// set shipping is 0
			//$order_totals[1]['value'] = 0;

			$order_totals[$count - 2]['title'] = 'Gesamtnetto';
			$order_totals[$count - 2]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;
			
			//print_r($order_totals); die();
		}
		
		usort($order_totals, function($a, $b) {
			return $a['sort_order'] - $b['sort_order']; // Ascending order
		});
		
		// END
		
		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
	
		$this->load->model('setting/setting');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
		
		if (!$from) {
			$from = $this->config->get('config_email');
		}

		$data['mail_header'] = MAILHEADER;
		$data['mail_footer'] = FOOTER;
		$data['pdf_address'] = PDF_ADDRESS;
		$data['ACCOUNT'] = ACCOUNT;
		$data['text_inform_order'] = $language->get('text_inform_order');
		$data['order_id'] = $order_info['order_id'];
		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];
		$data['total'] = number_format($order_total['value'], 2, ',', '.');
		
		$subject = SUBJECT_CRONTAB2; //html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
		$fromName = html_entity_decode('svapo.de, '.$order_info['store_name'], ENT_QUOTES, 'UTF-8');

		$message = $this->load->view('mail/order_notice_customer_14_days', $data);

		//create pdf
		/*$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		
		$pdf_name = 'Auftragsbestaetigung-svapo-'.$order_info['order_id'].'.pdf';
		$dompdf->loadHtml($this->load->view('mail/order_pdf', $data));
		$file_location = "./admin/auftrag/".$pdf_name;
		$dompdf->setPaper('A4', 'Horizontal');
		$dompdf->render();
		$pdf = $dompdf->output();
		file_put_contents($file_location, $pdf);*/

		//echo 'nam';

		$this->document->sendMailSMTP($order_info['email'], $subject, SMTP_USER, $fromName, $message, 'add', '');
		
		//$this->sendMail("vu@pixeldusche.com", $subject, SMTP_USER, $fromName, $message, 'add', $pdf_name, $order_status_id, $data);
	}

	public function countInvoiceNumber() {
		$query = $this->db->query("SELECT max(invoice_no) as maxNo FROM " . DB_PREFIX . "order WHERE invoice_no <> 0");
		
		return $query->rows[0]['maxNo'];
	}

	public function add($order_info, $order_status_id, $comment, $notify) {
		$this->load->model('account/customer');

		// get customer group id
		$customer_id = $order_info['customer_id'];
		//$customer_group_id = $this->model_account_customer->getGroupFromCustomer($customer_id);

		$order_id = $order_info['order_id'];

		// if is customer group special
		/*if($order_status_id != $this->getOrderStatusID($order_status_id, $customer_group_id)) {
			$order_status_id = $this->getOrderStatusID($order_status_id, $customer_group_id);

			$this->model_checkout_order->editStatusOrder($order_id, $order_status_id);
		}*/
		
		// Check for any downloadable products
		$download_status = false;

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
		
		foreach ($order_products as $order_product) {
			// Check if there are any linked downloads
			$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			if ($product_download_query->row['total']) {
				$download_status = true;
			}
		}
		
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['customer_id'] = $order_info['customer_id'];
		$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

		if ($download_status) {
			$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
		} else {
			$data['download'] = '';
		}

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = ($order_status_id != 17) ? $order_info['payment_method'] : 'vor Ort';
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($comment && $notify) {
			$data['comment'] = nl2br($comment);
		} else {
			$data['comment'] = '';
		}

		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$option_data = array();

			$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

			foreach ($order_options as $order_option) {
				if ($order_option['type'] != 'file') {
					$value = $order_option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $order_option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$data['products'][] = array(
				'name'     => $order_product['name'],
				'model'    => $order_product['model'],
				'option'   => $option_data,
				'quantity' => $order_product['quantity'],
				'price'    => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		// Vouchers
		$data['vouchers'] = array();

		$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_info['order_id']);

		foreach ($order_vouchers as $order_voucher) {
			$data['vouchers'][] = array(
				'description' => $order_voucher['description'],
				'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		// Order Totals
		$data['totals'] = array();
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);

		$this->document->displayOrder($order_totals, 0, 0, 0, 0, 0);
		
		// Add net price
		$count = count($order_totals);
		// if not coupon
		if($count == 4) {
			//print_r($order_totals); die();
			$order_totals[$count - 1] = $order_totals[$count];
		
			$order_totals[$count - 1]['title'] = 'Gesamtnetto';
			$order_totals[$count - 1]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;		
		} else { // if coupon
			//print_r($order_totals); die();
			// Because shipping = 0, so need to reduce total
			//$order_totals[$count - 1]['value'] = $order_totals[$count - 1]['value'] - $order_totals[1]['value'];

			// set shipping is 0
			//$order_totals[1]['value'] = 0;

			$order_totals[$count - 2]['title'] = 'Gesamtnetto';
			$order_totals[$count - 2]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;
			
			//print_r($order_totals); die();
		}
		
		usort($order_totals, function($a, $b) {
			return $a['sort_order'] - $b['sort_order']; // Ascending order
		});
		
		// END
		
		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
	
		$this->load->model('setting/setting');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
		
		if (!$from) {
			$from = $this->config->get('config_email');
		}

		$data['mail_header'] = MAILHEADER;
		$data['mail_footer'] = FOOTER;
		$data['pdf_address'] = PDF_ADDRESS;
		$data['ACCOUNT'] = ACCOUNT;
		$data['text_inform_order'] = $language->get('text_inform_order');

		$invoice_number = $this->countInvoiceNumber() + 1;
        $data['order_id'] = ($order_status_id != ORDER_ID) ? $order_info['order_id'] : $invoice_number;

		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];
		$data['total'] = number_format($order_total['value'], 2, ',', '.');
		
		/*$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($order_info['email']);
		$mail->setFrom($from);
		$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/order_add', $data));
		$mail->send();*/

		$subject = html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
		$fromName = html_entity_decode('svapo.de, '.$order_info['store_name'], ENT_QUOTES, 'UTF-8');

		$message = $this->load->view('mail/order_add_customer_process_2', $data);

		//create pdf
	/*$options = new Options();
	$options->set('tempDir', '/tmp');
	$options->set('chroot', __DIR__);    
	$options->set('isRemoteEnabled', TRUE);
	$dompdf = new Dompdf($options);
	// $dompdf->setHtmlFooter($htmlFooter);
	
	$dompdf->loadHtml($this->load->view('mail/order_pdf', $data));
	$dompdf->setPaper('A4', 'Horizontal');
	$dompdf->render();
	$pdf = $dompdf->output();
	$file_location = "./pdf/Rechnung-svapo.pdf";
	file_put_contents($file_location, $pdf);*/
	//end
	    //$count = count($order_totals);

		//print_r($order_total['value']); die(); 
		//create pdf
		$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		if($order_status_id != ORDER_ID) {
			$pdf_name = 'Auftragsbestaetigung-svapo-'.$order_info['order_id'].'.pdf';
            $dompdf->loadHtml($this->load->view('mail/order_pdf', $data));
		} else {
			$pdf_name = 'Rechnung-svapo-'.$order_info['order_id'].'.pdf';
            $dompdf->loadHtml($this->load->view('mail/order_pdf_invoice', $data));
		}
		
		$file_location = "./admin/auftrag/".$pdf_name;
		$dompdf->setPaper('A4', 'Horizontal');
		$dompdf->render();
		$pdf = $dompdf->output();
		file_put_contents($file_location, $pdf);
		
		//print_r($order_total); die();
		if($order_total['value'] > 0 && $notify)
		  $this->sendMail($order_info['order_id'], $order_info['email'], $subject, SMTP_USER, $fromName, $message, 'add', $pdf_name, $order_status_id, $data);
	}

	public function sendMail($order_id, $email, $subject, $SMTP_USER, $fromName, $message, $type, $pdf_name, $order_status_id = null, $data = null) {
		// if DHL
		if($order_status_id == ORDER_ID) {
			//create pdf and save
			$options = new Options();
			$options->set('tempDir', '/tmp');
			$options->set('chroot', __DIR__);    
			$options->set('isRemoteEnabled', TRUE);
			$dompdf = new Dompdf($options);

			$pdf_name_invoice = 'Rechnung-svapo-'.$order_id.'.pdf';

			$dompdf->loadHtml($this->load->view('mail/order_pdf_invoice', $data));

			$file_location = "./admin/invoice/".$pdf_name_invoice;

			$dompdf->setPaper('A4', 'Horizontal');
			$dompdf->render();
			$pdf = $dompdf->output();
			file_put_contents($file_location, $pdf);
			//end

			$message = $this->load->view('mail/order_pdf_invoice', $data);

			$this->document->sendMailSMTP($email, $subject, $SMTP_USER, $fromName, $message, $type, $pdf_name);
		}	
		// if order_status_id is in array of status_id
		else if(in_array($order_status_id, ORDER_STATUS_ID_ARRAY)) {
			// define message
			$message = $this->load->view('mail/order_add_customer_process_'.$order_status_id.'', $data);
			$this->document->sendMailSMTP($email, $subject, $SMTP_USER, $fromName, $message, $type, $pdf_name);
        } else { // if order_status_id is not in array of status_id
			$this->document->sendMailSMTP($email, $subject, $SMTP_USER, $fromName, $message, $type, $pdf_name);
        }
	}

	public function resend() {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
		$order_status_id = $order_info['order_status_id'];

        $this->add($order_info, $order_status_id, '', '');
	}

	public function noticeToCusmtomer() {
		$this->load->model('checkout/order');

		$orders = $this->model_checkout_order->getOrdersAvaiable();

		//print_r($orders); die();

		$message = '';
		$count = 0;

		$listSendMail = array();

		foreach($orders as $order) {
			$givenDate = new DateTime(date("Y-m-d", strtotime($order['date_added']))); // Replace with your date
			//$givenDate = new DateTime('2025-03-20'); // Replace with your date
			$today = new DateTime(); // Current date
			$interval = $today->diff($givenDate);

			//echo $interval->days; die();

			// if order is 7 days old
			if ($interval->days == DAY_CRONTAB1 + 1) {
				$count++;

                $order_info = $this->model_checkout_order->getOrder($order['order_id']);
				$order_status_id = $order_info['order_status_id'];

				$message .= 'Send mail to ' . $order_info['email'] . ' has order id ' . $order_info['order_id'] . ' has been created at ' . $order['date_added'] . '<br>';

				$listSendMail[] = $order_info;
            } 
		}

		echo ($count == 0) ? 'No Email was send' : 'Total '.$count.' mail have been sent with detail below <br><br>' . $message;
		
		// send mail to customer
		for($i = 0; $i < count($listSendMail); $i++)
		 $this->sendMailForOrderIs7day($listSendMail[$i], null, '', '');
	}

	public function crontab2() {
		$this->load->model('checkout/order');

		$orders = $this->model_checkout_order->getOrdersAvaiable();
		$new_status = STATUS_CANCEL;

		//$start_day = '2025-02-01';

		//print_r($orders); die();

		$message = '';
		$count = 0;

		$listSendMail = array();

		foreach($orders as $order) {
			$givenDate = new DateTime(date("Y-m-d", strtotime($order['date_added']))); // Replace with your date
			$today = new DateTime(); // Current date
			$interval = $today->diff($givenDate);

			//echo $interval->days; die(); 

			//$checkDate = ($givenDate >= new DateTime($start_day)) ? true : false;

			// if order is more than 10 days old
			if ($interval->days == DAY_CRONTAB2 + 1) {
				$count++;

				$order_id = $order['order_id'];
				$order_info = $this->model_checkout_order->getOrder($order_id);

				$order_status_id = $order_info['order_status_id'];
				
                $message .= 'Set status '.$new_status.' and back product to stock for ' . $order_info['email'] . ' has order id ' . $order_info['order_id'] . ' has been created at ' . $order['date_added'] . '<br>';

				$this->restore($order_id);

				//$this->sendMailForOrderIs14day($order_info, $order_status_id, '', '');
				$listSendMail[] = $order_info;
			} 
		}

		echo ($count == 0) ? 'No Order found' : 'Total '.$count.' orders have been found with detail below <br><br>' . $message;
		
		//flush(); 

		// send mail to customer
		for($i = 0; $i < count($listSendMail); $i++)
		   $this->sendMailForOrderIs14day($listSendMail[$i], null, '', '');
	}

	public function restore($order_id) {
		// back product of order to stock
		$order_products = $this->db->query("SELECT product_id, quantity FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		foreach ($order_products->rows as $product) {
			$this->db->query("UPDATE " . DB_PREFIX . "product 
							  SET quantity = quantity + " . (int)$product['quantity'] . " 
							  WHERE product_id = '" . (int)$product['product_id'] . "'");
		}
		// end 

		 // set status of order to 31
		 $status_id = STATUS_CANCEL;
		 $this->db->query("UPDATE " . DB_PREFIX . "order 
		 SET order_status_id = '" . (int)$status_id . "' 
		 WHERE order_id = '" . (int)$order_id . "'");
		 // end
    }

	public function getDataOfOrder($order_id) {
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');
		//$language->load('mail/order_add');

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
		//print_r($order_products); die();
		
		foreach ($order_products as $order_product) {
			// Check if there are any linked downloads
			$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			if ($product_download_query->row['total']) {
				$download_status = true;
			}
		}
		
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['customer_id'] = $order_info['customer_id'];
		$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

		if ($download_status) {
			$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
		} else {
			$data['download'] = '';
		}

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($comment) {
			$data['comment'] = nl2br($comment);
		} else {
			$data['comment'] = '';
		}

		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$option_data = array();

			$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

			foreach ($order_options as $order_option) {
				if ($order_option['type'] != 'file') {
					$value = $order_option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $order_option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$data['products'][] = array(
				'name'     => $order_product['name'],
				'model'    => $order_product['model'],
				'option'   => $option_data,
				'quantity' => $order_product['quantity'],
				'price_int' => $order_product['price'],
				'manufacture' => $order_product['manufacture'],
				'upc'         => $order_product['upc'], 
				'price'    => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		// Vouchers
		$data['vouchers'] = array();

		$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_info['order_id']);

		foreach ($order_vouchers as $order_voucher) {
			$data['vouchers'][] = array(
				'description' => $order_voucher['description'],
				'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		// Order Totals
		$data['totals'] = array();
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);

		// get value shipping of order
		$value_shipping = $this->config->get('shipping_flat_cost');

		foreach ($order_totals as $order_total) {
			if($order_total['code'] == 'sub_total') {
				$total = $order_total['value'];
				$minShipping = $this->config->get('shipping_free_total');

				if($total >= $minShipping)
				  $value_shipping = 0; 
            }
		}
        // end
		
        $this->document->displayOrder($order_totals, 0, 0, 0, 0, 0);

		$count = count($order_totals);

		// SET SHIPPING
		$order_totals[1]['value'] = $value_shipping;
		$order_totals[$count]['value'] = $order_totals[0]['value'] + $order_totals[1]['value'];
		$order_totals[2]['value'] = round( $order_totals[$count]['value'] - $order_totals[$count]['value']/1.19 ,2);
		// END

		// Add net price
		// if not coupon
		if($count == 4) {
			//print_r($order_totals); die();
			$order_totals[$count - 1] = $order_totals[$count];
		
			$order_totals[$count - 1]['title'] = 'Gesamtnetto';
			$order_totals[$count - 1]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;		
		} else { // if coupon
			//print_r($order_totals); die();
			// Because shipping = 0, so need to reduce total
			//$order_totals[$count - 1]['value'] = $order_totals[$count - 1]['value'] - $order_totals[1]['value'];

			// set shipping is 0
			//$order_totals[1]['value'] = 0;

			$order_totals[$count - 2]['title'] = 'Gesamtnetto';
			$order_totals[$count - 2]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;
			
			//print_r($order_totals); die();
		}
            
        $data['total'] = number_format($order_totals[$count]['value'], 2, ',', '.');
		

		usort($order_totals, function($a, $b) {
			return $a['sort_order'] - $b['sort_order']; // Ascending order
		});
		
        // END


		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
	
		$this->load->model('setting/setting');
		$this->load->model('checkout/order');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
		
		if (!$from) {
			$from = $this->config->get('config_email');
		}

		

		$invoiceNumber = ( $order_info['invoice_no'] == 0) ? $this->model_checkout_order->countInvoiceNumber() + 1 : $order_info['invoice_no'];
		
		$data['pdf_address'] = PDF_ADDRESS;
		$data['ACCOUNT'] = ACCOUNT;
		$data['mail_header'] = MAILHEADER;
		$data['mail_footer'] = FOOTER;
		$data['text_inform_order'] = $language->get('text_inform_order');

		$data['order_id'] = ( $order_status_id == ORDER_ID) ? $order_info['invoice_prefix'] . $invoiceNumber : $order_info['order_id'];
		$data['auftrag_id'] = $order_info['order_id'];

		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];

		$data['dhl'] = $this->model_checkout_order->getDHLOrder($order_info['order_id']);
		$dhl = json_decode($data['dhl']);

		$data['trackingnumber'] = '<a target="_blank" href="https://www.dhl.de/de/privatkunden/dhl-sendungsverfolgung.html?piececode='.$dhl->shipmentNo.'">' . $dhl->shipmentNo . '</a>';

	    return $data;
	}

	public function sendSignPDF() {
		$order_id = $this->request->get['order_id'];

		$order_info = $this->model_checkout_order->getOrder($order_id);

		$upload_file = $order_info['upload_file'];
		$upload_file = trim($upload_file);
		$upload_file = str_replace(' ', '', $upload_file);
		$upload_file = str_replace(['(', ')'], '', $upload_file);
		$upload_file = str_replace([':'], '-', $upload_file);

		$data = $this->getDataOfOrder($order_id);

		$this->exportPdfToSign($order_info, $data['products']);
		$status = 4;

		if(str_contains($order_info['upload_file'], 'prescription.pdf')) {
			$status = 4;
		}

		//create pdf
		$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		
		$pdf_name = 'Rechnung-svapo-'.$order_info['order_id'].'.pdf';
		$dompdf->loadHtml($this->load->view('mail/order_pdf_invoice', $data));
		$file_location = "./admin/invoice/".$pdf_name;
		//$status = 1;
		$dompdf->setPaper('A4', 'Horizontal');
		$dompdf->render();
		$pdf = $dompdf->output();
		// $file_location = "./pdf/Rechnung-svapo.pdf";
		file_put_contents($file_location, $pdf);
		//end
		// $subject = html_entity_decode(sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
		$subject = html_entity_decode(sprintf('%s - Rechnung - ' . $order_info['order_id'], 'svapo.de, '.$order_info['store_name']), ENT_QUOTES, 'UTF-8');
		$message = $this->load->view('mail/order_invoice', $data);

		$this->document->sendMailSMTP($order_info['email'], $subject, SMTP_USER, $from, $message, 'resend', false, $status, $upload_file);
	}

	public function edit($order_info, $order_status_id, $comment) {
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');
		//$language->load('mail/order_add');

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
		//print_r($order_products); die();
		
		foreach ($order_products as $order_product) {
			// Check if there are any linked downloads
			$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			if ($product_download_query->row['total']) {
				$download_status = true;
			}
		}
		
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['customer_id'] = $order_info['customer_id'];
		$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

		if ($download_status) {
			$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
		} else {
			$data['download'] = '';
		}

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($comment) {
			$data['comment'] = nl2br($comment);
		} else {
			$data['comment'] = '';
		}

		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$option_data = array();

			$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);

			foreach ($order_options as $order_option) {
				if ($order_option['type'] != 'file') {
					$value = $order_option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $order_option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$data['products'][] = array(
				'name'     => $order_product['name'],
				'model'    => $order_product['model'],
				'option'   => $option_data,
				'quantity' => $order_product['quantity'],
				'price_int' => $order_product['price'],
				'manufacture' => $order_product['manufacture'],
				'upc'         => $order_product['upc'], 
				'price'    => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		// Vouchers
		$data['vouchers'] = array();

		$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_info['order_id']);

		foreach ($order_vouchers as $order_voucher) {
			$data['vouchers'][] = array(
				'description' => $order_voucher['description'],
				'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		// Order Totals
		$data['totals'] = array();
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);

		// get value shipping of order
		$value_shipping = $this->config->get('shipping_flat_cost');

		foreach ($order_totals as $order_total) {
			if($order_total['code'] == 'sub_total') {
				$total = $order_total['value'];
				$minShipping = $this->config->get('shipping_free_total');

				if($total >= $minShipping)
				  $value_shipping = 0; 
            }
		}
        // end
		
        $this->document->displayOrder($order_totals, 0, 0, 0, 0, 0);

		$count = count($order_totals);

		// SET SHIPPING
		$order_totals[1]['value'] = $value_shipping;
		$order_totals[$count]['value'] = $order_totals[0]['value'] + $order_totals[1]['value'];
		$order_totals[2]['value'] = round( $order_totals[$count]['value'] - $order_totals[$count]['value']/1.19 ,2);
		// END

		// Add net price
		// if not coupon
		if($count == 4) {
			//print_r($order_totals); die();
			$order_totals[$count - 1] = $order_totals[$count];
		
			$order_totals[$count - 1]['title'] = 'Gesamtnetto';
			$order_totals[$count - 1]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;		
		} else { // if coupon
			//print_r($order_totals); die();
			// Because shipping = 0, so need to reduce total
			//$order_totals[$count - 1]['value'] = $order_totals[$count - 1]['value'] - $order_totals[1]['value'];

			// set shipping is 0
			//$order_totals[1]['value'] = 0;

			$order_totals[$count - 2]['title'] = 'Gesamtnetto';
			$order_totals[$count - 2]['value'] = round($order_totals[$count - 1]['value'] / 1.19);
			
			$order_totals[0]['sort_order'] = 0;
			$order_totals[1]['sort_order'] = 1;
			$order_totals[2]['sort_order'] = 2;
			$order_totals[4]['sort_order'] = 4;
			$order_totals[3]['sort_order'] = 3;
			
			//print_r($order_totals); die();
		}
            
        $data['total'] = number_format($order_totals[$count]['value'], 2, ',', '.');
		

		usort($order_totals, function($a, $b) {
			return $a['sort_order'] - $b['sort_order']; // Ascending order
		});
		
        // END


		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
	
		$this->load->model('setting/setting');
		$this->load->model('checkout/order');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
		
		if (!$from) {
			$from = $this->config->get('config_email');
		}

		

		$invoiceNumber = ( $order_info['invoice_no'] == 0) ? $this->model_checkout_order->countInvoiceNumber() + 1 : $order_info['invoice_no'];
		
		$data['pdf_address'] = PDF_ADDRESS;
		$data['ACCOUNT'] = ACCOUNT;
		$data['mail_header'] = MAILHEADER;
		$data['mail_footer'] = FOOTER;
		$data['text_inform_order'] = $language->get('text_inform_order');

		$data['order_id'] = ( $order_status_id == ORDER_ID) ? $order_info['invoice_prefix'] . $invoiceNumber : $order_info['order_id'];
		$data['auftrag_id'] = $order_info['order_id'];

		$data['firstname'] = $order_info['firstname'];
		$data['lastname'] = $order_info['lastname'];

		$data['dhl'] = $this->model_checkout_order->getDHLOrder($order_info['order_id']);
		$dhl = json_decode($data['dhl']);

		$data['trackingnumber'] = '<a target="_blank" href="https://www.dhl.de/de/privatkunden/dhl-sendungsverfolgung.html?piececode='.$dhl->shipmentNo.'">' . $dhl->shipmentNo . '</a>';

		//create pdf
		$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		
		$status = false;
		// If is DHL
		if($order_status_id == ORDER_ID) {
			$urlPDF = HTTP_SERVER . 'rEzEpT/' . $order_info['upload_file'];

			$upload_file = $order_info['upload_file'];
			$upload_file = trim($upload_file);
			$upload_file = str_replace(' ', '', $upload_file);
			$upload_file = str_replace(['(', ')'], '', $upload_file);
			$upload_file = str_replace([':'], '-', $upload_file);

			if($this->isPdf($urlPDF)) {
				//echo '1'; die();
				$this->exportPdfToSign($order_info, $data['products']);
				$status = 2;

				if(str_contains($order_info['upload_file'], 'prescription.pdf')) {
					$status = 3;
	            }
			} else {
				//echo '2'; die();
				$status = 3;
			}
            $pdf_name = 'Rechnung-svapo-'.$order_info['order_id'].'.pdf';
			$dompdf->loadHtml($this->load->view('mail/order_pdf_invoice', $data));
			$file_location = "./admin/invoice/".$pdf_name;
			//$status = 1;
			$dompdf->setPaper('A4', 'Horizontal');
			$dompdf->render();
			$pdf = $dompdf->output();
			// $file_location = "./pdf/Rechnung-svapo.pdf";
			file_put_contents($file_location, $pdf);
			//end
			// $subject = html_entity_decode(sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
			$subject = html_entity_decode(sprintf('%s - Rechnung - ' . $order_info['order_id'], 'svapo.de, '.$order_info['store_name']), ENT_QUOTES, 'UTF-8');
			$message = $this->load->view('mail/order_invoice', $data);
		  // if cancel
		  } /*else if(in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && !in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
			//$data['totals'][$count - 1]['text'] = '-' . $data['totals'][$count - 1]['text'];

			$pdf_name = 'Rechnung-svapo-'.$order_info['order_id'].'.pdf';
			$dompdf->loadHtml($this->load->view('mail/order_pdf_invoice', $data));
			$file_location = "./admin/invoice/".$pdf_name;
			$dompdf->setPaper('A4', 'Horizontal');
			$dompdf->render();
			$pdf = $dompdf->output();
			// $file_location = "./pdf/Rechnung-svapo.pdf";
			file_put_contents($file_location, $pdf);
			//end
		}*/
    	else {
			$subject = html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
			
			if($order_status_id == 25 || $order_status_id == 18)
			  $message = $this->load->view('mail/order_add_new', $data);
			// if not cancel
			else if ($order_status_id != STATUS1 && !($order_info['invoice_no'] > 0 && !in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))))
			  $message = $this->load->view('mail/order_edit', $data);
			// if is STATUS 7
			else if($order_status_id == STATUS1)
			$message = $this->load->view('mail/order_add_customer_process_'.$order_status_id.'', $data);
			// is cancel
			else {
				// PDF INVOICE
				$data['totals'][$count]['text'] = '-' . $data['totals'][$count]['text'];

				$data['order_id'] = str_replace('-00', '-', $order_info['invoice_prefix']. $order_info['invoice_no']);

				$pdf_name = 'Rechnung-svapo-'.$order_info['order_id'].'.pdf';
				$dompdf->loadHtml($this->load->view('mail/order_pdf_invoice_cancel', $data));
				$file_location = "./admin/invoice/".$pdf_name;
				$dompdf->setPaper('A4', 'Horizontal');
				$dompdf->render();
				$pdf = $dompdf->output();
				// $file_location = "./pdf/Rechnung-svapo.pdf";
				file_put_contents($file_location, $pdf);
				// END

				$upload_file = $pdf_name;
				$status = 2;
			
				$count = count($data['totals']);

				$data['totals'][$count]['text'] = $data['totals'][$count]['text'];
				$data['totals'][$count - 1]['text'] = '-' . $data['totals'][$count - 1]['text'];
				
				$subject = html_entity_decode(sprintf('Order STORNIERT', 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
			
				$message = $this->load->view('mail/order_cancel', $data);
			} 

            /*$subject = html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
			if($order_status_id == 25 || $order_status_id == 18)
			  $message = $this->load->view('mail/order_add_new', $data);
			else   
			  $message = $this->load->view('mail/order_edit', $data);*/
		} 
	
        $this->document->sendMailSMTP($order_info['email'], $subject, SMTP_USER, $from, $message, 'edit', $pdf_name, $status, $upload_file);
	}

	public function getDateOrder($order_info) {
		$date = $order_info['date_added'];

		$date = explode(' ', $date);
		$date = explode('-', $date[0]);

		//print_r($date); die();
		return $date[2]. '.' . $date[1]. '.' . $date[0];
	}

	public function setTextCenter($pdf, $text, $y) {
		// Get the width of the page
		$pageWidth = $pdf->GetPageWidth();

		// Calculate the width of the text
		$textWidth = $pdf->GetStringWidth($text);

		// Calculate the X position to center the text
		$xPosition = ($pageWidth - $textWidth) / 2 - 50;

		// Set the X position
		//$pdf->SetX($xPosition);

		// Add the text to the PDF
		//$pdf->Cell($textWidth, $y + 85, $text);
		$pdf->SetXY($xPosition, $y); // X and Y position
		// Add the text
		$pdf->Write(0, $text);
	}

	public function setTextRight($pdf, $text, $y) {
		// Get the width of the page
		$pageWidth = $pdf->GetPageWidth();

		// Calculate the width of the text
		$textWidth = $pdf->GetStringWidth($text);

		// Set the margin
        $marginRight = 13;  // Adjust this according to your needs

		//echo $textWidth . '//' . $pageWidth; die();

		// Calculate the X position to center the text
		$xPosition = $pageWidth - $textWidth - $marginRight;
		//echo $xPosition; die();

		// Set the X position
		$pdf->SetX($xPosition);
		//$pdf->SetY($y);
		

		// Add the text to the PDF
		//echo $y; die();
		$pdf->Cell($textWidth, -($y * 2 - 5), $text);
		//$pdf->SetXY($xPosition, $y); // X and Y position
		// Add the text
		//$pdf->Write(0, '2345,6');
	}

	public function convertPDFToVersionLow($inputFile, $outputFile, $pdfVersion) {
		// copy input file for the problem space
		$new_input = trim($inputFile);
		$new_input = str_replace(' ', '', $new_input);

		$new_input = str_replace(['(', ')'], '', $new_input);

		if($new_input != $inputFile)
		  copy($inputFile, $new_input);
		//  END 

		// delete old file
		if (file_exists($outputFile))
		  unlink($outputFile);
			
		// Ghostscript command to convert PDF to a specific version
		$command = "gs -dCompatibilityLevel=$pdfVersion -sDEVICE=pdfwrite -dNOPAUSE -dQUIET -dBATCH -sOutputFile=$outputFile $new_input";

		// Execute the command
		exec($command, $output, $return_var);

		/*if ($return_var === 0) {
			echo "PDF successfully converted to version $pdfVersion.";
		} else {
			echo "Error in converting PDF.";
		}*/
    }

	function isPdf($url) {
		if(strpos($url, 'PDF') !== false || strpos($url, 'pdf') !== false)
		  return true;
        else 
		  return false;
		//echo $url; die();

		// Fetch headers for the URL
		/*$headers = get_headers($url, 1); // The 1 argument returns the headers as an associative array
		
		// Check if Content-Type is set and is 'application/pdf'
		if (isset($headers['Content-Type'])) {
			// Check if the Content-Type is an array (it can happen when there are redirects)
			if (is_array($headers['Content-Type'])) {
				return in_array('application/pdf', $headers['Content-Type']);
			}
			
			// If not an array, simply compare
			return $headers['Content-Type'] === 'application/pdf';
		}
		
		return false;*/
	}

	public function uploadFileToLocal($localFilePath, $remoteFileUrl) {
		// Get the contents of the remote file
		$fileContents = file_get_contents($remoteFileUrl);

		if ($fileContents !== false) {
			// Save the file locally
			if (file_put_contents($localFilePath, $fileContents)) {
				//echo 'File successfully downloaded and saved locally.';
			} else {
				//echo 'Failed to save the file locally.';
			}
		} else {
			//echo 'Failed to retrieve the file from the remote server.';
		}
	}
	
	public function exportPdfToSign($order_info, $products) {
		$existLocalfile = true;

		//print_r($products); die();
		$upload_file = $order_info['upload_file'];

		$inputFile  = 'rEzEpT/' . $upload_file;

		// file on server
		$remoteFileUrl = PATH_FILE_UPLOAD . $upload_file;

		// upload file from server to local
		if(!file_exists($inputFile)) {
			$this->uploadFileToLocal($inputFile, $remoteFileUrl);
			$existLocalfile = false;
		}
		
		$outputFile = trim('pdf/' . 'DHL_' . $upload_file);
		//$outputFile = trim('pdf/' . 'upload.pdf');
		$outputFile = str_replace(' ', '', $outputFile);
		$outputFile = str_replace(['(', ')'], '', $outputFile);
		$outputFile = str_replace([':'], '-', $outputFile);
		
        //echo $outputFile; die();

		$this->convertPDFToVersionLow($inputFile, $outputFile, 1.4);
		//die();
		
		//print_r($order_info); die();
		// Path to the existing PDF
		$pdfFilePath = $outputFile; //'rEzEpT/' . $upload_file;
		//$pdfFilePath = 'rEzEpT/test_convert.pdf';
		
        $dateAdd = $this->getDateOrder($order_info);
		$phone = '5314146'; //$order_info['telephone'];

		// init x, y
		$initX = 130;
		$initY = 15;

		// nummer
		$spaceXNummer = 40;

		// total
		$spaceXTotal = 30;
		$spaceYTotal = 15;

		// area product
		$spaceYareaProduct = 32;
		$spaceY1areaProduct = 10;
		$spaceXEachProduct = 45;
		$spaceX1EachProduct = 10;

		// Create a new instance of FPDI
		$pdf = new FPDI();

		// Add the first page from the existing PDF
		$pageCount = $pdf->setSourceFile($pdfFilePath);
		$tplId = $pdf->importPage(1);

		// Get the size of the imported page
		$size = $pdf->getTemplateSize($tplId);
		//print_r($size); die();
		
		// Get the page height (assuming the unit is in mm, you might need to adjust depending on your settings)
		$pageHeight = $size['height'];
		
		// Calculate the current Y position
        $currentY = $pdf->GetY();

		$pdf->AddPage();
		$pdf->useTemplate($tplId);

		// Set font, size, and color
		$pdf->SetFont('Arial', '', 10);
		$pdf->SetTextColor(0, 0, 0);

		// date
		$pdf->SetXY($initX + 5, $initY + 2); // X and Y position
		// Add the text
		$pdf->Write(0, $dateAdd);

		// nummer
		$pdf->SetXY($initX + $spaceXNummer + 5, $initY + 2); // X and Y position
		// Add the phone
		$pdf->Write(0, $phone);

		// Product
		$position = 0;
		$totalProduct = 0;

		foreach($products as $product) {
			$name = '06460694'; //$product['name'];
			$quantity = $product['quantity'];
			$price = $product['price_int'];
			$total = $price * $quantity;
			
			$totalProduct += $total;

			// Add the name
			$pdf->SetXY($initX + 5 , $initY + $spaceYareaProduct + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, $name);

			// Add the quantity
			$pdf->SetXY($initX + $spaceXEachProduct , $initY + $spaceYareaProduct + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, 1);

			// Add the total
			//$total = 193;
			$total = number_format($total, 2, '.', ',');
			$newTotal = str_replace('.', ',', $total);
			$spaceTotal = strlen($newTotal) - 4;
			$pdf->SetXY($initX + $spaceXEachProduct + $spaceX1EachProduct + $spaceTotal , $initY + $spaceYareaProduct + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, $newTotal);
			//$this->setTextRight($pdf, str_replace('.', ',', $total), $initY + $spaceYareaProduct + $spaceY1areaProduct * $position);

			$position++;
		}

		// total of product
		$totalProduct = number_format($totalProduct, 2, '.', ',');
		//$totalProduct = 156.98;
		$spaceTotal = 162/(strlen(str_replace('.', ',', $totalProduct)) + 3);
		
		$pdf->SetXY($initX + $spaceXTotal + $spaceTotal, $initY + $spaceYTotal); // X and Y position
		$pdf->Write(0, str_replace('.', ',', $totalProduct));
		//$this->setTextRight($pdf, str_replace('.', ',', $totalProduct), $initY + $spaceYTotal);

		// temp
		$yLogo = $pageHeight/2;
		$xLogo = 30;
		$pdf->Image('pdf/svapo-stamp.png', $xLogo, $yLogo, 100, 56 );

		// add infor below temp
		/*$yLogo = $yLogo + 12;
		$this->setTextCenter($pdf, "Schloss Apotheke", $yLogo);
		$this->setTextCenter($pdf, "Apotheker Paschalis Papadopoulos", $yLogo + 5);
		$this->setTextCenter($pdf, iconv('utf-8', 'cp1252', "Bürgeler Str. 35 • 63075 Offenbach"), $yLogo + 10);
		$this->setTextCenter($pdf, "Tel: 0155 66 200 690", $yLogo + 15);
		$this->setTextCenter($pdf, "info@svapo.de", $yLogo + 20);*/

		$yLogo = $yLogo + 30;

		// add list of product below infor
		$position = 0;
		
        foreach($products as $product) {
			//print_r($product); die();
			$name = $product['quantity'] . 'g ' . $product['name'] . ' | ';
			//$manufacture = $product['manufacture'] . ' | ';
			$manufacture = $product['manufacture'];
			$chargenNumer = $product['upc'] . ' | ';

			$quantity = $product['quantity'];
			$price = $product['price_int'];

			$total = $price * $quantity;
			$total = number_format($total, 2, '.', ',');
			$total = $total . '€';
			$total = str_replace('.', ',', $total);
			
			// Add the name
			$space = 30;
			$spaceY1areaProduct = 5;//$spaceY1areaProduct;
			$spaceXEachProduct = $spaceXEachProduct + 30;
			
			$pdf->SetXY($xLogo, $yLogo + $space + $spaceY1areaProduct * $position); // X and Y position
			//$pdf->Write(0, iconv('utf-8', 'cp1252', $name . $manufacture . $chargenNumer . $total));
			$pdf->Write(0, iconv('utf-8', 'cp1252', $name . $manufacture));
			
            /*$space_1 = 5;
			$increase = 1.7;

			// Add the manufacture
			$pdf->SetXY($xLogo + $space_1 + strlen($name) * $increase, $yLogo + $space + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, $manufacture);

			// Add the chargen nummer
			$pdf->SetXY($xLogo + $space_1 * 1.5 + strlen($name) * $increase + strlen($manufacture) * $increase, $yLogo + $space + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, $chargenNumer);

			// Add the total
			$pdf->SetXY($xLogo + $space_1 * 2.5 + strlen($name) * $increase + strlen($manufacture) * $increase + strlen($chargenNumer) * $increase, $yLogo + $space + $spaceY1areaProduct * $position); // X and Y position
			$pdf->Write(0, iconv('utf-8', 'cp1252', $total)); */

			$position++;
		}
		
		// Output the new PDF
		$newPdfFilePath = $outputFile; //'pdf/sign-pdf-test.pdf';
		// BJOERN STOPPED GENERATE PDF
		$pdf->Output($newPdfFilePath, 'F');
		// upload file to server
		$this->uploadFileToserver($newPdfFilePath);
		// delete file reciepe of DHL on local
		$this->deleteFile($newPdfFilePath);
		// if not implement before, then delete local
		if(!$existLocalfile)
		$this->deleteFile($inputFile);
	}

	public function deleteFile($file) {
		if (file_exists($file)) {
			if (unlink($file)) {
				//echo "File deleted successfully.";
			} else {
				//echo "Error: Could not delete the file.";
			}
		}
	}

	public function uploadFileToserver($filePath) {
		// URL of the target server where the file will be uploaded
		$targetUrl = URL_UPLOAD;
		
		// Initialize cURL session
		$ch = curl_init();
		
		// Set cURL options
		curl_setopt($ch, CURLOPT_URL, $targetUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
		
		// Attach the file using the 'file' key
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			'file' => new CURLFile($filePath)
		]);
		
		// Execute cURL request and capture the response
		$response = curl_exec($ch);
		
		// Check for errors
		if (curl_errno($ch)) {
			return false;
		} else {
			return true;
		}
		
		// Close cURL sessi
	}
	
    // Admin Alert Mail
	public function alert(&$route, &$args) {
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}
		
		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	
		
		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}
		
		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array)$this->config->get('config_mail_alert'))) {	
			$this->load->language('mail/order_alert');
			
			// HTML Mail
			$data['text_received'] = $this->language->get('text_received');
			$data['text_order_id'] = $this->language->get('text_order_id');
			$data['text_date_added'] = $this->language->get('text_date_added');
			$data['text_order_status'] = $this->language->get('text_order_status');
			$data['text_product'] = $this->language->get('text_product');
			$data['text_total'] = $this->language->get('text_total');
			$data['text_comment'] = $this->language->get('text_comment');
			
			$data['order_id'] = $order_info['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($order_status_query->num_rows) {
				$data['order_status'] = $order_status_query->row['name'];
			} else {
				$data['order_status'] = '';
			}

			$this->load->model('tool/upload');
			
			$data['products'] = array();

			$order_products = $this->model_checkout_order->getOrderProducts($order_id);

			foreach ($order_products as $order_product) {
				$option_data = array();
				
				$order_options = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);
				
				foreach ($order_options as $order_option) {
					if ($order_option['type'] != 'file') {
						$value = $order_option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);
	
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $order_option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
				}
					
				$data['products'][] = array(
					'name'     => $order_product['name'],
					'model'    => $order_product['model'],
					'quantity' => $order_product['quantity'],
					'option'   => $option_data,
					'total'    => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);
			}
			
			$data['vouchers'] = array();
			
			$order_vouchers = $this->model_checkout_order->getOrderVouchers($order_id);

			foreach ($order_vouchers as $order_voucher) {
				$data['vouchers'][] = array(
					'description' => $order_voucher['description'],
					'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);					
			}

			$data['totals'] = array();
			
			$order_totals = $this->model_checkout_order->getOrderTotals($order_id);

			foreach ($order_totals as $order_total) {
				$data['totals'][] = array(
					'title' => $order_total['title'],
					'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);
			}

			$data['comment'] = strip_tags($order_info['comment']);

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/order_alert', $data));
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				$email = trim($email);
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}
}
