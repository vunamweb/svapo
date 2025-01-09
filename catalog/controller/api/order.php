<?php
include "./dompdf/autoload.inc.php";
//require_once('./fpdi/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;

class ControllerApiOrder extends Controller {
	public function getRequireAttribute() {
		return [
			'customer' => [
				'firstname',
				'lastname',
				'email',
				'phone',
				'homeAddress' => [
					 'streetName',
					 'postalCode',
					 'city',
					 'houseNr'
			   ]
			],
			'products' => [
				'id' => "array",
				'quantity'
			]
		];
	}

	public function getRequireAttributeDocnow24() {
		return [
			'customer' => [
				'firstname',
				'lastname',
				'email',
				'phone',
				'homeAddress' => [
					 'streetName',
					 'postalCode',
					 'city',
					 'houseNr'
			   ]
			],
			'products' => [
				'ansayProductId' => "array",
				'quantity'
			]
		];
	}

	// Function to get the Authorization header
	public function getAuthorizationHeader() {
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER['Authorization']);
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			// Handle Apache server environment variable
			$headers = trim($_SERVER['HTTP_AUTHORIZATION']);
		} elseif (function_exists('apache_request_headers')) {
			if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
			  $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
			else {
				$requestHeaders = apache_request_headers();
				if (isset($requestHeaders['Authorization'])) {
					$headers = trim($requestHeaders['Authorization']);
				}
			}  
		}

		return $headers;
	}

	// Function to get the Bearer token from the header
	public function getBearerToken() {
		$headers = $this->getAuthorizationHeader();
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}

	public function checkAttributes($attributes, $jsonArray, string $parentKey = ''): bool {
		$rawData = file_get_contents("php://input");

		//if(!$this->model_checkout_order->checkExistAttribute($jsonArray['pharmacy_id'], $jsonArray['internalOrderId']))
	      //return false; 
		//print_r($jsonArray); die();
		foreach ($attributes as $key => $value) {
			// if is parent
			if (is_array($value)) {
				// Check for nested attributes
				if (!isset($jsonArray[$key])) {
					echo json_encode(['error_codes' => 402, 'error' => "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key)]);
					$this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key));
					return false;
				}
				if (!$this->checkAttributes($value, $jsonArray[$key], $parentKey ? "$parentKey.$key" : $key)) {
					return false;
				}
			} else { // if is child
				/*if($value == 'quantity') {
					print_r($jsonArray);
				    die();
				}*/
				// if is array
				if($value == 'array') {
					//echo '111'; die();
					//print_r($jsonArray);
					if(!$this->model_catalog_product->checkExistListProducts($jsonArray, $key, $parentKey))
					 return false;

					//return true; 
				}
				else if ( (!is_array($jsonArray) || !array_key_exists($value, $jsonArray)) && !$this->checkPropertyInArrayJson($value, $jsonArray) ) {
					echo json_encode(['error_codes' => 402, 'error' => "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$value" : $value)]);
					$this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$value" : $value));
					
					return false;
				}
			}
		}

		return true;
	}

	public function checkAttributesDocnow24($attributes, $jsonArray, string $parentKey = ''): bool {
		$rawData = file_get_contents("php://input");

		//if(!$this->model_checkout_order->checkExistAttribute($jsonArray['pharmacy_id'], $jsonArray['internalOrderId']))
	      //return false; 
		//print_r($jsonArray); die();
		foreach ($attributes as $key => $value) {
			// if is parent
			if (is_array($value)) {
				// Check for nested attributes
				if (!isset($jsonArray[$key])) {
					echo json_encode(['error_codes' => 402, 'error' => "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key)]);
					$this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key));
					return false;
				}
				if (!$this->checkAttributesDocnow24($value, $jsonArray[$key], $parentKey ? "$parentKey.$key" : $key)) {
					return false;
				}
			} else { // if is child
				/*if($value == 'quantity') {
					print_r($jsonArray);
				    die();
				}*/
				// if is array
				if($value == 'array') {
					//echo '111'; die();
					//print_r($jsonArray);
					if(!$this->model_catalog_product->checkExistListProducts($jsonArray, $key, $parentKey))
					 return false;

					//return true; 
				}
				else if ( (!is_array($jsonArray) || !array_key_exists($value, $jsonArray)) && !$this->checkPropertyInArrayJson($value, $jsonArray) ) {
					echo json_encode(['error_codes' => 402, 'error' => "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$value" : $value)]);
					$this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$value" : $value));
					
					return false;
				}
			}
		}

		return true;
	}

	public function checkPropertyInArrayJson($key, $arrayJson) {
		foreach ($arrayJson as $obj) {
			if(!is_array($obj))
			return false;

			if (!array_key_exists($key, $obj) || !is_int($obj[$key])) {
				return false;
			}
		}

		return true;
	}


	public function sendMail($order_info, $order_status_id = 0, $customer_group_id, $deliveryType) {
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
			//echo $order_info['shipping_address_format']; die();
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
			'address_1' => $order_info['payment_address_1'],
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

		//print_r($order_totals); die();
		
		// Add net price
		$count = count($order_totals);
		//echo $count; die();
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
		
		$subject = html_entity_decode(sprintf($language->get('text_subject'), 'svapo.de, '.$order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8');
		$fromName = html_entity_decode('svapo.de, '.$order_info['store_name'], ENT_QUOTES, 'UTF-8');

		if($this->checkdeliveryType($deliveryType))
		  $message = $this->load->view('mail/order_add_customer_process_17', $data);
		else
		  $message = ($customer_group_id == CUSTOMER_GROUP_ID) ? $this->load->view('mail/order_add_customer_ansay_special', $data) : $this->load->view('mail/order_add_customer_ansay', $data);

		$options = new Options();
		$options->set('tempDir', '/tmp');
		$options->set('chroot', __DIR__);    
		$options->set('isRemoteEnabled', TRUE);
		$dompdf = new Dompdf($options);
		// $dompdf->setHtmlFooter($htmlFooter);
		
		$pdf_name = 'Auftragsbestaetigung-svapo-'.$order_info['order_id'].'.pdf';
		$dompdf->loadHtml($this->load->view('mail/order_ansay_pdf', $data));
		$file_location = "./admin/auftrag/".$pdf_name;
		$dompdf->setPaper('A4', 'Horizontal');
		$dompdf->render();
		$pdf = $dompdf->output();
		file_put_contents($file_location, $pdf);
		
		$this->document->sendMailSMTP($order_info['email'], $subject, SMTP_USER, $fromName, $message, 'add', $pdf_name);
	}

	public function checkdeliveryType($deliveryType) {
		return (strtolower($deliveryType) == strtolower('pickup')) ? true : false;
	}
	
	public function addOrder() {
		$this->load->model('catalog/product');
		$this->load->model('checkout/order');

		$rawData = file_get_contents("php://input");

		$this->model_checkout_order->saveJSONAnsay($rawData);

		try {
			// Check for the Bearer token
			$token = $this->getBearerToken();
			//echo $token; die();
			if ($token === null) {
				// Bearer token is missing
				header('HTTP/1.0 401 Unauthorized');
				echo json_encode(['error_codes' => 401, 'error' => 'Missing Bearer token']);

				$this->document->writeLog($rawData, 'Missing Bearer token');
				exit;
			}
	
			if ($token != TOKEN) {
				// Bearer token is missing
				header('HTTP/1.0 401 Unauthorized');
				echo json_encode(['error_codes' => 401, 'error' => 'Unauthorized']);

				$this->document->writeLog($rawData, 'Unauthorized');
				exit;
			}
			
			// $_POST wird nur gefüllt, wenn die Daten in einem application/x-www-form-urlencoded oder multipart/form-data Format gesendet werden, das üblicherweise beim Senden von HTML-Formularen verwendet wird.
			// php://input wird verwendet, wenn die Daten z. B. als JSON, XML oder ein anderes benutzerdefiniertes Format gesendet werden, das nicht in den Standard-$_POST-Array eingefügt wird.
			// Check JSON
			// file_get_contents("php://input") ist sehr nützlich, wenn du den vollständigen Rohinhalt einer Anfrage brauchst, besonders wenn du mit APIs oder JSON-Daten arbeitest, die als Teil des HTTP-Requests gesendet werden.
			//$rawData = file_get_contents("php://input");
	
			// Decode the JSON data
			$jsonData = json_decode($rawData, true);
			$deliveryType = $jsonData['deliveryType'];
			//echo $deliveryType; die();
	
			// Check if the JSON data is valid and not empty
			if (json_last_error() !== JSON_ERROR_NONE) {
				// The JSON is not valid
				http_response_code(400); // Bad Request
				echo json_encode(['error_codes' => 400, 'error' => 'Invalid JSON']);

				$this->document->writeLog($rawData, 'Invalid JSON');
				exit;
			}
	
			if (empty($jsonData)) {
				// The JSON is empty or not present
				http_response_code(400); // Bad Request
				echo json_encode(['error_codes' => 400, 'error' => 'Missing JSON body']);

				$this->document->writeLog($rawData, 'Missing JSON body');
				exit;
			}
	
			if($this->checkAttributes($this->getRequireAttribute(), $jsonData)) {
			$response = $this->saveOrder($jsonData);

			//print_r($response); die();

			$order_id = $response->order_id;
			$customer_group_id = $response->customer_group_id;
	
			 if($order_id) {
				$order_status_id = ($customer_group_id == CUSTOMER_GROUP_ID || $this->checkdeliveryType($deliveryType)) ? ORDER_STATUS_ID : 18;
	
				$this->model_checkout_order->updateStatusOrder($order_status_id, $order_id);
	
				$urlDocument = $jsonData['prescriptionURL'];
				$this->saveDocumentToServer($urlDocument, $order_id);
	
				// SEND MAIL
				$order_info = $this->model_checkout_order->getOrder($order_id);
				$this->sendMail($order_info, $order_status_id, $customer_group_id, $deliveryType);
				// END
	
				echo json_encode(['codes' => 200, 'order_id' => $order_id]);
			} else {
				$dataJSON = json_decode($rawData);

				$errorLog = 'Error while creating order' . '<br>' . PHP_EOL;
				$errorLog .= $dataJSON->customer->firstname . ' ' . $dataJSON->customer->lastname;
				
				echo json_encode(['error_codes' => 401, 'error' => $errorLog]);
				$this->document->writeLog($rawData, $errorLog);
			 }
			}
		} catch (\Exception $e) {
			echo json_encode(['error_codes' => 401, 'error' => $e->getMessage()]);
			$this->document->writeLog($rawData, $e->getMessage());
		} catch (\Throwable $e) {
			echo json_encode(['error_codes' => 401, 'error' => $e->getMessage()]);
			$this->document->writeLog($rawData, $e->getMessage());
			//echo $e->getMessage();	
		  // log error here by write $e->getMessage() in log file
		} 
	}
	
	public function addOrderDocnow24() {
		$this->load->model('catalog/product');
		$this->load->model('checkout/order');

		$rawData = file_get_contents("php://input");

		$this->model_checkout_order->saveJSONAnsay($rawData);

		try {
			// Check for the Bearer token
			$token = $this->getBearerToken();
			//echo $token; die();
			/*if ($token === null) {
				// Bearer token is missing
				header('HTTP/1.0 401 Unauthorized');
				echo json_encode(['error_codes' => 401, 'error' => 'Missing Bearer token']);

				$this->document->writeLog($rawData, 'Missing Bearer token');
				exit;
			}
	
			if ($token != TOKEN) {
				// Bearer token is missing
				header('HTTP/1.0 401 Unauthorized');
				echo json_encode(['error_codes' => 401, 'error' => 'Unauthorized']);

				$this->document->writeLog($rawData, 'Unauthorized');
				exit;
			}*/
			
			// $_POST wird nur gefüllt, wenn die Daten in einem application/x-www-form-urlencoded oder multipart/form-data Format gesendet werden, das üblicherweise beim Senden von HTML-Formularen verwendet wird.
			// php://input wird verwendet, wenn die Daten z. B. als JSON, XML oder ein anderes benutzerdefiniertes Format gesendet werden, das nicht in den Standard-$_POST-Array eingefügt wird.
			// Check JSON
			// file_get_contents("php://input") ist sehr nützlich, wenn du den vollständigen Rohinhalt einer Anfrage brauchst, besonders wenn du mit APIs oder JSON-Daten arbeitest, die als Teil des HTTP-Requests gesendet werden.
			//$rawData = file_get_contents("php://input");
	
			// Decode the JSON data
			$jsonData = json_decode($rawData, true);
			$deliveryType = $jsonData['deliveryType'];
			//echo $deliveryType; die();
	
			// Check if the JSON data is valid and not empty
			if (json_last_error() !== JSON_ERROR_NONE) {
				// The JSON is not valid
				http_response_code(400); // Bad Request
				echo json_encode(['error_codes' => 400, 'error' => 'Invalid JSON']);

				$this->document->writeLog($rawData, 'Invalid JSON');
				exit;
			}
	
			if (empty($jsonData)) {
				// The JSON is empty or not present
				http_response_code(400); // Bad Request
				echo json_encode(['error_codes' => 400, 'error' => 'Missing JSON body']);

				$this->document->writeLog($rawData, 'Missing JSON body');
				exit;
			}
	
			if($this->checkAttributesDocnow24($this->getRequireAttributeDocnow24(), $jsonData)) {
			$response = $this->saveOrder($jsonData);

			//print_r($response); die();

			$order_id = $response->order_id;
			$customer_group_id = $response->customer_group_id;
	
			 if($order_id) {
				$order_status_id = ($customer_group_id == CUSTOMER_GROUP_ID || $this->checkdeliveryType($deliveryType)) ? ORDER_STATUS_ID : 25;
	
				$this->model_checkout_order->updateStatusOrder($order_status_id, $order_id);
	
				$urlDocument = $jsonData['prescriptionURL'];
				$this->saveDocumentToServer($urlDocument, $order_id);
	
				// SEND MAIL
				$order_info = $this->model_checkout_order->getOrder($order_id);
				$this->sendMail($order_info, $order_status_id, $customer_group_id, $deliveryType);
				// END
	
				echo json_encode(['codes' => 200, 'order_id' => $order_id]);
			} else {
				$dataJSON = json_decode($rawData);

				$errorLog = 'Error while creating order' . '<br>' . PHP_EOL;
				$errorLog .= $dataJSON->customer->firstname . ' ' . $dataJSON->customer->lastname;
				
				echo json_encode(['error_codes' => 401, 'error' => $errorLog]);
				$this->document->writeLog($rawData, $errorLog);
			 }
			}
		} catch (\Exception $e) {
			echo json_encode(['error_codes' => 401, 'error' => $e->getMessage()]);
			$this->document->writeLog($rawData, $e->getMessage());
		} catch (\Throwable $e) {
			echo json_encode(['error_codes' => 401, 'error' => $e->getMessage()]);
			$this->document->writeLog($rawData, $e->getMessage());
			//echo $e->getMessage();	
		  // log error here by write $e->getMessage() in log file
		} 
    }
	
	public function converListOfSkuToID($products) {
	   $result = array();
	   
	   foreach ($products as $product) {
		   $product_id = $this->model_catalog_product->getIdFromSku($product['id']);

		   $product['id'] = $product_id;

		   $result[] = $product;
		}
		
		return $result;
	}
	
	public function converListOfMpnToID($products) {
		$result = array();
		
		foreach ($products as $product) {
		   if($product['ansayProductId']) {
			 //echo '1'; die();  
			 $product_id = $this->model_catalog_product->getIdFromMpn($product['ansayProductId']);
		   } else {
			 //echo '2'; die();  
			 $product_id = $this->model_catalog_product->getIdFromMpn($product['id']);
		   } 
	 
		   $product['id'] = $product_id;
		 
		   $result[] = $product;
		 }
		 
		 return $result;
	 }

	public function uploadFileToserver($filePath) {
		// URL of the target server where the file will be uploaded
		$targetUrl = URL_UPLOAD;

		//echo $targetUrl . '///' . $filePath; die();
		
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

	public function saveDocumentToServer($base64_string, $order_id) {
		$mesage_error = 'Order created successfully, However upload document was failed because decode base 64 prescription URL' . 
			'is not correct, maybe this url contains special character, Please correct it';

		// Directory where you want to save the file
		$saveToDir = 'rEzEpT/'; // Make sure the folder has write permissions

		// Filename (optional, you can also extract it from the URL)
		$filename = 'document_ansay_' . $order_id . '_' . date("Y-m-dH-i-s") . '.pdf';

		// Complete path to save the file
		$savePath = $saveToDir . $filename;

		try {
			// Remove the headers (if any) from the Base64 string
            // Base64 string might have a "data:application/pdf;base64," prefix
			if (strpos($base64_string, 'data:application/pdf;base64,') === 0) {
				$base64_string = substr($base64_string, strlen('data:application/pdf;base64,'));
			}

			// Decode the Base64 string
            $fileContent = base64_decode($base64_string);

			// Save the file to the server
			if (file_put_contents($savePath, $fileContent)) {
			   // save name of file of order in database
			   $this->model_checkout_order->editPhotoOrder($order_id, $filename);
			   // upload file to server
			   $this->uploadFileToserver($savePath);
			   // delete file on local
			   if (file_exists($savePath)) {
				if (unlink($savePath)) {
					//echo "File deleted successfully.";
				} else {
					//echo "Error: Could not delete the file.";
				}
			} else {
				//echo "Error: File does not exist.";
			}
					//echo "File downloaded and saved successfully to $savePath";
			} else {
					//echo "Failed to save the file.";
			}
			
		} catch (\Exception $e) {
			echo json_encode(['codes' => 200, 'order_id' => $order_id, 'message' => 'prescriptionURL:' . $e->getMessage() ]);

			die();
		} catch (\Throwable $e) {
			echo json_encode(['codes' => 200, 'order_id' => $order_id, 'message' => 'prescriptionURL: ' .  $e->getMessage() ]);

			die();
		} 
	}

	public function getShipping($total, $customer_group_id, $deliveryType) {
		//echo $total; die();
		$obj = new \stdClass;

	   $valueShipping = $this->config->get('shipping_flat_cost');
	   $minShipping = $this->config->get('shipping_free_total');

	   if($total >= $minShipping || $customer_group_id == CUSTOMER_GROUP_ID || $this->checkdeliveryType($deliveryType)) {
		   // $obj->title = 'Abholung von Geschäft';
		   $obj->title = 'Versandkostenfrei';
		   $obj->value = 0;
	   } else {
		$obj->title = 'Versandkostenpauschale';
		$obj->value = $valueShipping ;
	   }

	   return $obj;
	}

	public function saveOrder($data) {
	   $deliveryType = $data['deliveryType'];
	   //print_r($data); die();	
	   $data['products'] = $this->converListOfMpnToID($data['products']);

	   $products = $data['products'];

	   // save products into cart  
	   $this->saveProductIntoCart($products);

	   // Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');

				break;
			}
		}

		if ($this->cart->hasProducts()) {
			$order_data = array();

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($total_data['totals'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data['totals']);

			$order_data = array_merge($order_data, $total_data);

			$order_data['totals'][2] = $order_data['totals'][1];

			//print_r($order_data['totals']); die();

			$this->load->model('account/customer');

			$email_customer = $data['customer']['email'];

			$customerByEmail = $this->model_account_customer->getCustomerByEmail($email_customer);

			$customer_id = $customerByEmail ? $customerByEmail['customer_id'] : 0;

			$customer_group_id = $this->model_account_customer->getGroupFromCustomer($customer_id) ? $this->model_account_customer->getGroupFromCustomer($customer_id) : 1;

			//echo 'customer_id: ' . $customer_id . '///' . 'customer_group_id: ' . $customer_group_id; die();
			
            $shipping = $this->getShipping($order_data['totals'][0]['value'], $customer_group_id, $deliveryType);

			$order_data['totals'][1]['code'] = 'shipping'; 
			// $order_data['totals'][1]['title'] = 'Abholung von Geschäft'; 
			$order_data['totals'][1]['title'] = $shipping->title;
			$order_data['totals'][1]['value'] = $shipping->value;
			$order_data['totals'][1]['sort_order'] = 3;
			
			$order_data['totals'][2]['value'] = $order_data['totals'][0]['value'] + $order_data['totals'][1]['value'];
			
            //print_r($order_data['totals'][1]); die();

            //$order_data['totals'] = $totals;

			$this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}
			
			$order_data['customer_id'] = $customer_id;
			$order_data['customer_group_id'] = $customer_group_id; //1;
			$order_data['firstname'] = $data['customer']['firstname'];
			$order_data['lastname'] = $data['customer']['lastname'];
			$order_data['email'] = $email_customer; //$data['customer']['email'];
			$order_data['telephone'] = $data['customer']['phone'];
			$order_data['custom_field'] = array();
			
            $order_data['payment_firstname'] = $data['customer']['firstname'];
			$order_data['payment_lastname'] = $data['customer']['lastname'];
			$order_data['payment_company'] = '';
			$order_data['payment_address_1'] = ' ' . $data['customer']['homeAddress']['streetName'].' '.$data['customer']['homeAddress']['houseNr'] . ' ';
			$order_data['payment_address_2'] = '';
			$order_data['payment_city'] =  $data['customer']['homeAddress']['city'] . ' ';
			$order_data['payment_postcode'] = $data['customer']['homeAddress']['postalCode'];
			$order_data['payment_zone'] = 'Thüringen';
			$order_data['payment_zone_id'] = 1269;
			$order_data['payment_country'] = 'Germany';
			$order_data['payment_country_id'] = 81;
			$order_data['payment_address_format'] = '{company}{firstname} {lastname}{address_1}{address_2}{postcode} {city}{country}';
			$order_data['payment_custom_field'] = array();

			$order_data['payment_method'] = 'Banküberweisung';
			$order_data['payment_code'] = 'bank_transfer';

			$order_data['shipping_method'] = 'Versandkostenpauschale';
			$order_data['shipping_code'] = 'flat.flat';

			if (isset($data['customer']['deliveryAddress'])) {
				$order_data['shipping_firstname'] = $data['customer']['firstname'];
				$order_data['shipping_lastname'] =  $data['customer']['lastname'];
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = ' ' . $data['customer']['deliveryAddress']['streetName'] . ' ';
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = $data['customer']['deliveryAddress']['city'] . ' ';
				$order_data['shipping_postcode'] = $data['customer']['deliveryAddress']['postalCode'];
				$order_data['shipping_zone'] = 'Thüringen';
				$order_data['shipping_zone_id'] = 1269;
				$order_data['shipping_country'] = 'Germany';
				$order_data['shipping_country_id'] = 81;
				$order_data['shipping_address_format'] = '{company}{firstname} {lastname}  {address_1}{address_2}  {postcode} {city}{country}';
				$order_data['shipping_custom_field'] = array();
            } else {
				$order_data['shipping_firstname'] = $data['customer']['firstname'];
				$order_data['shipping_lastname'] =  $data['customer']['lastname'];
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = $order_data['payment_address_1'];
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = $order_data['payment_city'];
				$order_data['shipping_postcode'] = $order_data['payment_postcode'];
				$order_data['shipping_zone'] = 'Thüringen';
				$order_data['shipping_zone_id'] = 1269;
				$order_data['shipping_country'] = 'Germany';
				$order_data['shipping_country_id'] = 81;
				$order_data['shipping_address_format'] = '{company}{firstname} {lastname}{address_1}{address_2}{postcode} {city}{country}';
				$order_data['shipping_custom_field'] = array();
			}

			$order_data['products'] = array();

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

			$order_data['comment'] = $this->getDaTaComment($data);
			$order_data['total'] = $total_data['total'];

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = 'EUR';
			$order_data['currency_value'] = 1.00000000;
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

			//print_r($order_data); die();

			$order_id = $this->model_checkout_order->addOrder($order_data);
			$this->session->data['order_id'] = $order_id;

			$this->cart->clear();

			$obj = new \stdClass;
			$obj->order_id = $order_id;
			$obj->customer_group_id = $customer_group_id;

			return $obj;
        }
	}
  
	public function saveProductIntoCart($products) {
		foreach($products as $product) {
			$product_id = $product['id'];
			$quantity = $product['quantity'];

			$option = array();
			$recurring_id = 0;

			$this->cart->add($product_id, $quantity, $option, $recurring_id);
        }
	}

	public function getDaTaComment($jsonArray) {
		$comment = '';
		$break = '<br>';

		if(isset($jsonArray['pharmacy_id']))
		  $comment .= 'Pharmacy_id: ' . $jsonArray['pharmacy_id'] . $break;

		if(isset($jsonArray['doctor'])) {
			$comment .= 'Data of Doctor below' . $break;

			$comment .= isset($jsonArray['doctor']['name']) ? 'Name: ' . $jsonArray['doctor']['name'] . $break : null;
			$comment .= isset($jsonArray['doctor']['firstname']) ? 'Firstname: ' . $jsonArray['doctor']['firstname'] . $break : null;
			$comment .= isset($jsonArray['doctor']['phone']) ? 'Phone: ' . $jsonArray['doctor']['phone'] . $break : null;
			$comment .= isset($jsonArray['doctor']['cityOfSignature']) ? 'CityOfSignature: ' . $jsonArray['doctor']['cityOfSignature'] . $break : null;
			$comment .= isset($jsonArray['doctor']['dateOfSignature']) ? 'DateOfSignature: ' . $jsonArray['doctor']['dateOfSignature'] . $break : null;
		} 
		
		if(isset($jsonArray['internalOrderId'])) {
			$comment .= $break . 'InternalOrderId: ' . $jsonArray['internalOrderId'] . $break;
		}

		if(isset($jsonArray['isThirdPartyPrescription'])) {
			$comment .= $break . 'isThirdPartyPrescription: ' . $jsonArray['isThirdPartyPrescription'] . $break;
		}

		return $comment;
	}

	public function removeSpecialCharacter($fileName) {
		$upload_file = $fileName;

		$upload_file = trim($upload_file);
		$upload_file = str_replace(' ', '', $upload_file);
		$upload_file = str_replace(['(', ')'], '', $upload_file);

		return $upload_file;
	}

	public function uploadFile() {
		//echo 'ddd'; die();
		$this->load->model('checkout/order');

		$order_id = $this->request->get['order_id'];

		$namePhoto = $_FILES["upload_file"]["name"];

        $namePhoto = $this->removeSpecialCharacter($namePhoto);
		$namePhoto = date("Y-m-dH-i-s") . '_' . $namePhoto;

		$tmpFilePath = $_FILES["upload_file"]['tmp_name'];
		$fileName = $namePhoto;

		// Open the file with CURLFile
		$cfile = new CURLFile($tmpFilePath, $file['type'], $fileName);
    
		// Target server URL
		$url = URL_UPLOAD;
	
		// Prepare the data for the POST request
		$postData = [
			'file' => $cfile,
			// Add additional fields here if required
			'additional_field' => 'value'
		];
	
		// Initialize cURL session
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Execute cURL and get the response
		$response = curl_exec($ch);
	
		// Check for errors
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		} else {
			$this->model_checkout_order->editPhotoOrder($order_id, $namePhoto);
			echo 'Response from server: ' . $response;
		}
		
		// Close cURL session
		curl_close($ch);

		//echo $namePhoto . 'ddd'; die();

		/*$targetDirectory = "rEzEpT/"; // Directory where uploaded files will be saved
		$targetFile = $targetDirectory . basename($namePhoto); // Get the file name
		
		// Try to upload the file
			if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $targetFile)) {
				$this->model_checkout_order->editPhotoOrder($order_id, $namePhoto);
			} else {
				echo "Sorry, there was an error uploading your file.";
			}*/
	}

	public function add() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Customer
			if (!isset($this->session->data['customer'])) {
				$json['error'] = $this->language->get('error_customer');
			}

			// Payment Address
			if (!isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Payment Method
			if (!$json && !empty($this->request->post['payment_method'])) {
				if (empty($this->session->data['payment_methods'])) {
					$json['error'] = $this->language->get('error_no_payment');
				} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
					$json['error'] = $this->language->get('error_payment_method');
				}

				if (!$json) {
					$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
				}
			}

			if (!isset($this->session->data['payment_method'])) {
				$json['error'] = $this->language->get('error_payment_method');
			}

			// Shipping
			if ($this->cart->hasShipping()) {
				// Shipping Address
				if (!isset($this->session->data['shipping_address'])) {
					$json['error'] = $this->language->get('error_shipping_address');
				}

				// Shipping Method
				if (!$json && !empty($this->request->post['shipping_method'])) {
					if (empty($this->session->data['shipping_methods'])) {
						$json['error'] = $this->language->get('error_no_shipping');
					} else {
						$shipping = explode('.', $this->request->post['shipping_method']);

						if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
							$json['error'] = $this->language->get('error_shipping_method');
						}
					}

					if (!$json) {
						$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
					}
				}

				// Shipping Method
				if (!isset($this->session->data['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			} else {
				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			}

			// Cart
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$json['error'] = $this->language->get('error_stock');
			}

			// Validate minimum quantity requirements.
			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

					break;
				}
			}

			if (!$json) {
				$json['success'] = $this->language->get('text_success');
				
				$order_data = array();

				// Store Details
				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');
				$order_data['store_url'] = $this->config->get('config_url');

				// Customer Details
				$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
				$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
				$order_data['firstname'] = $this->session->data['customer']['firstname'];
				$order_data['lastname'] = $this->session->data['customer']['lastname'];
				$order_data['email'] = $this->session->data['customer']['email'];
				$order_data['telephone'] = $this->session->data['customer']['telephone'];
				$order_data['custom_field'] = $this->session->data['customer']['custom_field'];

				// Payment Details
				$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
				$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
				$order_data['payment_company'] = $this->session->data['payment_address']['company'];
				$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
				$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
				$order_data['payment_city'] = $this->session->data['payment_address']['city'];
				$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
				$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
				$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
				$order_data['payment_country'] = $this->session->data['payment_address']['country'];
				$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
				$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
				$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

				if (isset($this->session->data['payment_method']['title'])) {
					$order_data['payment_method'] = $this->session->data['payment_method']['title'];
				} else {
					$order_data['payment_method'] = '';
				}

				if (isset($this->session->data['payment_method']['code'])) {
					$order_data['payment_code'] = $this->session->data['payment_method']['code'];
				} else {
					$order_data['payment_code'] = '';
				}

				// Shipping Details
				if ($this->cart->hasShipping()) {
					$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
					$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
					$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
					$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
					$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
					$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
					$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
					$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
					$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
					$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
					$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
					$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
					$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

					if (isset($this->session->data['shipping_method']['title'])) {
						$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
					} else {
						$order_data['shipping_method'] = '';
					}

					if (isset($this->session->data['shipping_method']['code'])) {
						$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
					} else {
						$order_data['shipping_code'] = '';
					}
				} else {
					$order_data['shipping_firstname'] = '';
					$order_data['shipping_lastname'] = '';
					$order_data['shipping_company'] = '';
					$order_data['shipping_address_1'] = '';
					$order_data['shipping_address_2'] = '';
					$order_data['shipping_city'] = '';
					$order_data['shipping_postcode'] = '';
					$order_data['shipping_zone'] = '';
					$order_data['shipping_zone_id'] = '';
					$order_data['shipping_country'] = '';
					$order_data['shipping_country_id'] = '';
					$order_data['shipping_address_format'] = '';
					$order_data['shipping_custom_field'] = array();
					$order_data['shipping_method'] = '';
					$order_data['shipping_code'] = '';
				}

				// Products
				$order_data['products'] = array();

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

				// Gift Voucher
				$order_data['vouchers'] = array();

				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$order_data['vouchers'][] = array(
							'description'      => $voucher['description'],
							'code'             => token(10),
							'to_name'          => $voucher['to_name'],
							'to_email'         => $voucher['to_email'],
							'from_name'        => $voucher['from_name'],
							'from_email'       => $voucher['from_email'],
							'voucher_theme_id' => $voucher['voucher_theme_id'],
							'message'          => $voucher['message'],
							'amount'           => $voucher['amount']
						);
					}
				}

				// Order Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;

				// Because __call can not keep var references so we put them into an array.
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);
			
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($total_data['totals'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $total_data['totals']);

				$order_data = array_merge($order_data, $total_data);

				if (isset($this->request->post['comment'])) {
					$order_data['comment'] = $this->request->post['comment'];
				} else {
					$order_data['comment'] = '';
				}

				if (isset($this->request->post['affiliate_id'])) {
					$subtotal = $this->cart->getSubTotal();

					// Affiliate
					$this->load->model('account/customer');

					$affiliate_info = $this->model_account_customer->getAffiliate($this->request->post['affiliate_id']);

					if ($affiliate_info) {
						$order_data['affiliate_id'] = $affiliate_info['customer_id'];
						$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
					} else {
						$order_data['affiliate_id'] = 0;
						$order_data['commission'] = 0;
					}

					// Marketing
					$order_data['marketing_id'] = 0;
					$order_data['tracking'] = '';
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
					$order_data['marketing_id'] = 0;
					$order_data['tracking'] = '';
				}

				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				$order_data['currency_code'] = $this->session->data['currency'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
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

				if($this->request->post['order_status_id'] == ORDER_STATUS_ID)
				  $this->setNoShipping($order_data);
				
                $json['order_id'] = $this->model_checkout_order->addOrder($order_data);

				// Set the order history
				if (isset($this->request->post['order_status_id'])) {
					$order_status_id = $this->request->post['order_status_id'];
				} else {
					$order_status_id = $this->config->get('config_order_status_id');
				}

				$this->model_checkout_order->addOrderHistory($json['order_id'], $order_status_id);
				
				// clear cart since the order has already been successfully stored.
				$this->cart->clear();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				// Customer
				if (!isset($this->session->data['customer'])) {
					$json['error'] = $this->language->get('error_customer');
				}

				// Payment Address
				if (!isset($this->session->data['payment_address'])) {
					$json['error'] = $this->language->get('error_payment_address');
				}

				// Payment Method
				if (!$json && !empty($this->request->post['payment_method'])) {
					if (empty($this->session->data['payment_methods'])) {
						$json['error'] = $this->language->get('error_no_payment');
					} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
						$json['error'] = $this->language->get('error_payment_method');
					}

					if (!$json) {
						$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
					}
				}

				if (!isset($this->session->data['payment_method'])) {
					$json['error'] = $this->language->get('error_payment_method');
				}

				// Shipping
				if ($this->cart->hasShipping()) {
					// Shipping Address
					if (!isset($this->session->data['shipping_address'])) {
						$json['error'] = $this->language->get('error_shipping_address');
					}

					// Shipping Method
					if (!$json && !empty($this->request->post['shipping_method'])) {
						if (empty($this->session->data['shipping_methods'])) {
							$json['error'] = $this->language->get('error_no_shipping');
						} else {
							$shipping = explode('.', $this->request->post['shipping_method']);

							if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
								$json['error'] = $this->language->get('error_shipping_method');
							}
						}

						if (!$json) {
							$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
						}
					}

					if (!isset($this->session->data['shipping_method'])) {
						$json['error'] = $this->language->get('error_shipping_method');
					}
				} else {
					unset($this->session->data['shipping_address']);
					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
				}

				// Cart
				if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
					$json['error'] = $this->language->get('error_stock');
				}

				// Validate minimum quantity requirements.
				$products = $this->cart->getProducts();

				foreach ($products as $product) {
					$product_total = 0;

					foreach ($products as $product_2) {
						if ($product_2['product_id'] == $product['product_id']) {
							$product_total += $product_2['quantity'];
						}
					}

					if ($product['minimum'] > $product_total) {
						$json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

						break;
					}
				}

				if (!$json) {
					$json['success'] = $this->language->get('text_success');
					
					$order_data = array();

					// Store Details
					$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
					$order_data['store_id'] = $this->config->get('config_store_id');
					$order_data['store_name'] = $this->config->get('config_name');
					$order_data['store_url'] = $this->config->get('config_url');

					// Customer Details
					$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
					$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
					$order_data['firstname'] = $this->session->data['customer']['firstname'];
					$order_data['lastname'] = $this->session->data['customer']['lastname'];
					$order_data['email'] = $this->session->data['customer']['email'];
					$order_data['telephone'] = $this->session->data['customer']['telephone'];
					$order_data['custom_field'] = $this->session->data['customer']['custom_field'];

					// Payment Details
					$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
					$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
					$order_data['payment_company'] = $this->session->data['payment_address']['company'];
					$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
					$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
					$order_data['payment_city'] = $this->session->data['payment_address']['city'];
					$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
					$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
					$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
					$order_data['payment_country'] = $this->session->data['payment_address']['country'];
					$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
					$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
					$order_data['payment_custom_field'] = $this->session->data['payment_address']['custom_field'];

					if (isset($this->session->data['payment_method']['title'])) {
						$order_data['payment_method'] = $this->session->data['payment_method']['title'];
					} else {
						$order_data['payment_method'] = '';
					}

					if (isset($this->session->data['payment_method']['code'])) {
						$order_data['payment_code'] = $this->session->data['payment_method']['code'];
					} else {
						$order_data['payment_code'] = '';
					}

					// Shipping Details
					if ($this->cart->hasShipping()) {
						$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
						$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
						$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
						$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
						$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
						$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
						$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
						$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
						$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
						$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
						$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
						$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
						$order_data['shipping_custom_field'] = $this->session->data['shipping_address']['custom_field'];

						if (isset($this->session->data['shipping_method']['title'])) {
							$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
						} else {
							$order_data['shipping_method'] = '';
						}

						if (isset($this->session->data['shipping_method']['code'])) {
							$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
						} else {
							$order_data['shipping_code'] = '';
						}
					} else {
						$order_data['shipping_firstname'] = '';
						$order_data['shipping_lastname'] = '';
						$order_data['shipping_company'] = '';
						$order_data['shipping_address_1'] = '';
						$order_data['shipping_address_2'] = '';
						$order_data['shipping_city'] = '';
						$order_data['shipping_postcode'] = '';
						$order_data['shipping_zone'] = '';
						$order_data['shipping_zone_id'] = '';
						$order_data['shipping_country'] = '';
						$order_data['shipping_country_id'] = '';
						$order_data['shipping_address_format'] = '';
						$order_data['shipping_custom_field'] = array();
						$order_data['shipping_method'] = '';
						$order_data['shipping_code'] = '';
					}

					// Products
					$order_data['products'] = array();

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

					// Gift Voucher
					$order_data['vouchers'] = array();

					if (!empty($this->session->data['vouchers'])) {
						foreach ($this->session->data['vouchers'] as $voucher) {
							$order_data['vouchers'][] = array(
								'description'      => $voucher['description'],
								'code'             => token(10),
								'to_name'          => $voucher['to_name'],
								'to_email'         => $voucher['to_email'],
								'from_name'        => $voucher['from_name'],
								'from_email'       => $voucher['from_email'],
								'voucher_theme_id' => $voucher['voucher_theme_id'],
								'message'          => $voucher['message'],
								'amount'           => $voucher['amount']
							);
						}
					}

					// Order Totals
					$this->load->model('setting/extension');

					$totals = array();
					$taxes = $this->cart->getTaxes();
					$total = 0;
					
					// Because __call can not keep var references so we put them into an array. 
					$total_data = array(
						'totals' => &$totals,
						'taxes'  => &$taxes,
						'total'  => &$total
					);
			
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);
							
							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($total_data['totals'] as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $total_data['totals']);

					$order_data = array_merge($order_data, $total_data);
					//print_r($order_data['totals']); die();

					if (isset($this->request->post['comment'])) {
						$order_data['comment'] = $this->request->post['comment'];
					} else {
						$order_data['comment'] = '';
					}

					if (isset($this->request->post['affiliate_id'])) {
						$subtotal = $this->cart->getSubTotal();

						// Affiliate
						$this->load->model('account/customer');

						$affiliate_info = $this->model_account_customer->getAffiliate($this->request->post['affiliate_id']);

						if ($affiliate_info) {
							$order_data['affiliate_id'] = $affiliate_info['customer_id'];
							$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
						} else {
							$order_data['affiliate_id'] = 0;
							$order_data['commission'] = 0;
						}
					} else {
						$order_data['affiliate_id'] = 0;
						$order_data['commission'] = 0;
					}

					//print_r($order_data); die();
					// if status is 17, then set shipping is 0
					if($this->request->post['order_status_id'] == ORDER_STATUS_ID)
					  $this->setNoShipping($order_data);

					$this->model_checkout_order->editOrder($order_id, $order_data);

					// Set the order history
					if (isset($this->request->post['order_status_id'])) {
						$order_status_id = $this->request->post['order_status_id'];
					} else {
						$order_status_id = $this->config->get('config_order_status_id');
					}
					
					$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);

					// When order editing is completed, delete added order status for Void the order first.
					if ($order_status_id) {
						$this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int)$order_id . "' AND order_status_id = '0'");
					}
				}
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function setNoShipping(&$order_data) {
		//print_r($order_data); die();
		$order_data['payment_method'] = 'Zahlung bei Abholung';
		$order_data['payment_code'] = 'cod';
		
		$order_data['totals'][1]['title'] = 'Versandkostenfrei';
		$order_data['totals'][1]['value'] = 0;

		$order_data['totals'][2]['value'] = $order_data['totals'][0]['value'] + $order_data['totals'][1]['value'];
	}

	public function delete() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->model_checkout_order->deleteOrder($order_id);

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function info() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$json['order'] = $order_info;

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function history() {
		$this->load->language('api/order');

		$this->load->model('checkout/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'order_status_id',
				'notify',
				'override',
				'comment'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			$this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			/*if(str_contains($order_info['upload_file'], 'prescription.pdf')) {
				$json['error'] = 'Stop send for order has file upload prescription.pdf';

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));

				return;
			}*/
			//print_r($order_info); die();

			if ($order_info) {
				if($this->request->post['order_status_id'] == ORDER_ID && $this->model_checkout_order->getDHLOrder($order_id) == ''){
					$response = $this->sendDhlShipmentRequest($order_id);
					//print_r($response); die();
		
					//if($response == null) {
					if(isset($response['status']) && isset($response['status']['status']) && $response['status']['status'] == 200) {
						$obj = new \stdClass;
						$obj->label = $response['items'][0]['label'];
						$obj->shipmentRefNo = $response['items'][0]['shipmentRefNo'];
						$obj->shipmentNo = $response['items'][0]['shipmentNo'];
						$obj->routingCode = $response['items'][0]['routingCode'];
						
		
						$this->model_checkout_order->updateDHLOrder(json_encode($obj), $order_id);
		
						//print_r($obj); die();
					}
				}
				
				$this->model_checkout_order->addOrderHistory($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']);

				// if DHL is ok
				if(isset($response['status']) && isset($response['status']['status']) && $response['status']['status'] == 200)
				//if($response == null)
				  $json['success'] = $this->language->get('text_success');
				// if DHL has error
				else
				  $json['error'] = $response;

			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		if(str_contains($order_info['upload_file'], 'prescription.pdf')) {
				$json['error'] = 'Stop send for order has file upload prescription.pdf';
        }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
      
	public function getTotalWeight($products) {
		$total_weight = 0;

		foreach ($products as $product) {
			$total_weight += $product['weight'] * $product['quantity'];
		}

		return $total_weight;
	}

	public function getTotalHeight($products) {
		$total_height = 0;

		foreach ($products as $product) {
			$total_height += $product['height'] * $product['quantity'];
		}

		return $total_height;

	}

	public function getTotalLength($products) {
		$total_length = 0;

		foreach ($products as $product) {
			$total_length += $product['length'] * $product['quantity'];
		}

		return $total_length;

	}

	public function getTotalWidth($products) {
		$total_width = 0;

		foreach ($products as $product) {
			$total_width += $product['width'] * $product['quantity'];
		}

		return $total_width;

	}

	public function separateAddress($adresse) {
		$obj = new \stdClass;

		$text = nl2br(htmlspecialchars($adresse));

		$text = explode("<br />", $text);

		$obj->address = $text[0];

		$city_postcode = $text[1];
		$city_postcode = explode(" ", $city_postcode);

		$obj->postcode = preg_replace('/\s+/', '', $city_postcode[0]);
		$obj->city = $city_postcode[1] . ' ' . $city_postcode[2] . ' ' . $city_postcode[3];

		return $obj;
    }

	public function getAddressOfOrder($order_info) {
	   return trim($order_info['payment_address_1']);
	   //print_r($order_info); die();	
       $address_shipping = $order_info['shipping_address_1'];
	   $address_payment = $order_info['payment_address_1'];

	   // if address shipping containers number  
	   if (preg_match('/\d/', $address_shipping)) {
		return $address_shipping;
	   } else { // if address shipping not containers number  
		return $address_payment;
	   }
	}

	public function sendDhlShipmentRequest($order_id) {
		$url = 'https://api-eu.dhl.com/parcel/de/shipping/v2/orders';

        $username = $this->config->get('config_geocode');
		$password = $this->config->get('config_fax');
		
		$apiKey = 'EEKBudZ96102qzCKEkowt5ACl7y9dFtn';

		$order_info = $this->model_checkout_order->getOrder($order_id);

		$products = $this->model_checkout_order->getOrderProductsImprove($order_id);

		$total_height = $this->getTotalHeight($products);
		$total_length = $this->getTotalLength($products);
		$total_width = $this->getTotalWidth($products);

		$total_weight = $this->getTotalWeight($products) < 500 ? 500 : $this->getTotalWeight($products);

		//print_r($order_info); die();
        //print_r($products); die(); 
		// EMAIL AND PHONE OF OWNER SHOP
		$email1 = $this->config->get('config_email');
		$phone1 = $this->config->get('config_telephone');
		
		// EMAIL AND PHONE OF CUSTOMER
		$email2 = $order_info['email'];
		$phone2 = $order_info['telephone'];

		$name1 = $this->config->get('config_name');

		$address = $this->separateAddress($this->config->get('config_address'));
		//print_r($address); die();
		$address1 = $address->address;
		//echo $address1; die();
        $address2_1 = '';
		$postCode1 = $address->postcode;
		//echo $postCode1; die();
        $city1 = $address->city;
		//echo $city1 . '////'; die();
		$country1 = 'DEU';
		//echo $country1 . '/////';
		//die();
		
        $name2 = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
		$address2 = $this->getAddressOfOrder($order_info); //$order_info['shipping_address_1'];
		//echo $address2; die();
		$postCode2 = $order_info['shipping_postcode'];
		$postCode2 = str_replace('<br>', '', $postCode2);

		$city2 = $order_info['shipping_city'];
		$country2 = $order_info['shipping_iso_code_3'];
		
		// Versanddetails
		$shipmentDetails = '
		{
			"profile": "STANDARD_GRUPPENPROFIL",
			"shipments": [
			{
				"product": "V01PAK",
				"billingNumber": "63773090770101",
				"refNo": "'.$order_info['invoice_prefix'].' - '.$order_id.'",
				"shipper": {
				"name1": "'.$name1.'",
				"addressStreet": "'.$address1.'",
				"additionalAddressInformation1": "'.$address2_1.' ",
				"postalCode": "'.$postCode1.'",
				"city": "'.$city1.'",
				"country": "'.$country1.'",
				"email": "'.$email1.'",
				"phone": "'.$phone1.'"
				},
				"consignee": {
				"name1": "'.$name2.'",
				"addressStreet": "'.$address2.'",
				"postalCode": "'.$postCode2.'",
				"city": "'.$city2.'",
				"country": "'.$country2.'",
				"email": "'.$email2.'",
				"phone": "'.$phone2.'"
				},
				"details": {
				"dim": {
					"uom": "cm",
					"height": 15,
					"length": 20,
					"width": 16
				},
				"weight": {
					"uom": "g",
					"value": 900
				}
				},
				"services": {
					"parcelOutletRouting": "'.$email2.'"
				}
			}
			]
		}
		';

		/*if($order_id == 9014) {
			print_r($shipmentDetails); 
			die();
		}*/
		

		$ch = curl_init($url);
		
		$auth = base64_encode("$username:$password");
		
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Basic ' . $auth,
			'dhl-api-key: ' . $apiKey
		];
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $shipmentDetails);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if (curl_errno($ch)) {
			return 'Error';
			//echo 'cURL Error: ' . curl_error($ch);
		} else {
			if ($httpCode == 200) {
				//return null;
				return json_decode($response, true);
			} else {
				return $response;
				//echo "HTTP Request failed. Status code: $httpCode. Response: $response";
			}
		}
		
		curl_close($ch);
		return null;
	}
}
