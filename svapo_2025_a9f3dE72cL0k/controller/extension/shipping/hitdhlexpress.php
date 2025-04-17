<?php
class ControllerExtensionShippinghitdhlexpress extends Controller {
	private $error = array();
	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hittech_dhl_details_new` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` text NOT NULL,
		  `tracking_num` text NOT NULL,
		  `shipping_label` text COLLATE utf8_bin NOT NULL,
		  `invoice` text COLLATE utf8_bin NOT NULL,
		  `return_label` text COLLATE utf8_bin  NULL,
		  `return_invoice` text COLLATE utf8_bin  NULL,
		  `one` text COLLATE utf8_bin  NULL,
		  `two` text COLLATE utf8_bin  NULL,
		  `three` text COLLATE utf8_bin  NULL,
		  PRIMARY KEY (`id`)
		)");

	$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_hsncode` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL,
	`name` text NOT NULL,
	`hsncode` int(20) NOT NULL,
	PRIMARY KEY (`id`)
  )");
	}
	public function index() {
		$this->install();
		$this->load->language('extension/shipping/hitdhlexpress');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($_POST['btn_licence_activation'])) {
			if (!$this->request->post['shipping_hitdhlexpress_licence_email'] && empty($this->request->post['shipping_hitdhlexpress_licence_email'])) {
				$this->error['warning'] = 'Enter Purchased Email Address';
			} else {
				$licence_email = trim($this->request->post['shipping_hitdhlexpress_licence_email']);
			}
			if (!$this->request->post['shipping_hitdhlexpress_licence_licence'] && empty($this->request->post['shipping_hitdhlexpress_licence_licence'])) {
				$this->error['warning'] = 'Enter Licence Key';
			} else {
				$licence_key = trim($this->request->post['shipping_hitdhlexpress_licence_licence']);
			}

			if (isset($licence_key) && isset($licence_email)) {
				$licenc_check_url = "https://hittechmarket.com/wp-json/license/activation";
				// $licenc_check_url = "http://localhost/wordpress/wp-json/license/activation";
				$req_data = array("email" => $licence_email, "license_key" => $licence_key, "platform" => 3);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, $licenc_check_url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req_data));
				$result = curl_exec($ch);
				curl_close($ch);
				$obj = json_decode($result);
				if(isset($obj->status) && $obj->status == "success")
				{
					$this->request->post['shipping_hitdhlexpress_licence_expires'] = $obj->valid_upto;
					$this->request->post['shipping_hitdhlexpress_licence_status_licence'] = 'Activated';
				} elseif (isset($obj->msg)) {
					$this->error['warning'] = (string)$obj->msg;
				} else {
					$this->error['warning'] = 'Unknown error occured, please try again later.';
				}
			}
			$this->model_setting_setting->editSetting('shipping_hitdhlexpress_licence', $this->request->post);
		}else if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($_POST['btn_renew_activation'])) {
			if (!$this->request->post['shipping_hitdhlexpress_renew_email'] && empty($this->request->post['shipping_hitdhlexpress_renew_email'])) {
				$this->error['warning'] = 'Enter Purchased Email Address.';
			} else {
				$licence_email = trim($this->request->post['shipping_hitdhlexpress_renew_email']);
			}
			if (!$this->request->post['shipping_hitdhlexpress_renew_licence'] && empty($this->request->post['shipping_hitdhlexpress_renew_licence'])) {
				$this->error['warning'] = 'Enter Licence Key.';
			} else {
				$licence_key = trim($this->request->post['shipping_hitdhlexpress_renew_licence']);
			}

			if (isset($licence_key) && isset($licence_email)) {
				$licenc_check_url = "https://hittechmarket.com/wp-json/license/activation";
				// $licenc_check_url = "http://localhost/wordpress/wp-json/license/activation";
				$req_data = array("email" => $licence_email, "license_key" => $licence_key, "platform" => 3);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, $licenc_check_url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req_data));
				$result = curl_exec($ch);
				curl_close($ch);
				$obj = json_decode($result);
				
				if(isset($obj->status) && $obj->status == "success")
				{
					$this->request->post['shipping_hitdhlexpress_renew_expires'] = $obj->valid_upto;
				} elseif (isset($obj->msg)) {
					$this->error['warning'] = (string)$obj->msg;
				} else {
					$this->error['warning'] = 'Unknown error occured, please try again later.';
				}
			}
		$this->model_setting_setting->editSetting('shipping_hitdhlexpress_renew', $this->request->post);
			
		}else if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($_POST['btn_reset_activation'])) {
			
			$this->request->post['shipping_hitdhlexpress_licence_expires'] = '';
			$this->request->post['shipping_hitdhlexpress_licence_status_licence'] = '';

		}else if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_hitdhlexpress', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['account'])) {
			$data['error_account'] = $this->error['account'];
		} else {
			$data['error_account'] = '';
		}
		
		if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}

		if (isset($this->error['dimension'])) {
			$data['error_dimension'] = $this->error['dimension'];
		} else {
			$data['error_dimension'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/hitdhlexpress', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/hitdhlexpress', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		
		if (isset($this->request->post['shipping_hitdhlexpress_test'])) {
			$data['shipping_hitdhlexpress_test'] = $this->request->post['shipping_hitdhlexpress_test'];
		} else {
			$data['shipping_hitdhlexpress_test'] = $this->config->get('shipping_hitdhlexpress_test');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_apitype'])) {
			$data['shipping_hitdhlexpress_apitype'] = $this->request->post['shipping_hitdhlexpress_apitype'];
		} else {
			$data['shipping_hitdhlexpress_apitype'] = $this->config->get('shipping_hitdhlexpress_apitype');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_key'])) {
			$data['shipping_hitdhlexpress_key'] = $this->request->post['shipping_hitdhlexpress_key'];
		} else {
			$data['shipping_hitdhlexpress_key'] = $this->config->get('shipping_hitdhlexpress_key');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_password'])) {
			$data['shipping_hitdhlexpress_password'] = $this->request->post['shipping_hitdhlexpress_password'];
		} else {
			$data['shipping_hitdhlexpress_password'] = $this->config->get('shipping_hitdhlexpress_password');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_account'])) {
			$data['shipping_hitdhlexpress_account'] = $this->request->post['shipping_hitdhlexpress_account'];
		} else {
			$data['shipping_hitdhlexpress_account'] = $this->config->get('shipping_hitdhlexpress_account');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_status'])) {
			$data['shipping_hitdhlexpress_status'] = $this->request->post['shipping_hitdhlexpress_status'];
		} else {
			$data['shipping_hitdhlexpress_status'] = $this->config->get('shipping_hitdhlexpress_status');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_sort_order'])) {
			$data['shipping_hitdhlexpress_sort_order'] = $this->request->post['shipping_hitdhlexpress_sort_order'];
		} else {
			$data['shipping_hitdhlexpress_sort_order'] = $this->config->get('shipping_hitdhlexpress_sort_order');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_shipper_name'])) {
			$data['shipping_hitdhlexpress_shipper_name'] = $this->request->post['shipping_hitdhlexpress_shipper_name'];
		} else {
			$data['shipping_hitdhlexpress_shipper_name'] = $this->config->get('shipping_hitdhlexpress_shipper_name');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_company_name'])) {
			$data['shipping_hitdhlexpress_company_name'] = $this->request->post['shipping_hitdhlexpress_company_name'];
		} else {
			$data['shipping_hitdhlexpress_company_name'] = $this->config->get('shipping_hitdhlexpress_company_name');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_phone_num'])) {
			$data['shipping_hitdhlexpress_phone_num'] = $this->request->post['shipping_hitdhlexpress_phone_num'];
		} else {
			$data['shipping_hitdhlexpress_phone_num'] = $this->config->get('shipping_hitdhlexpress_phone_num');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_email_addr'])) {
			$data['shipping_hitdhlexpress_email_addr'] = $this->request->post['shipping_hitdhlexpress_email_addr'];
		} else {
			$data['shipping_hitdhlexpress_email_addr'] = $this->config->get('shipping_hitdhlexpress_email_addr');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_address1'])) {
			$data['shipping_hitdhlexpress_address1'] = $this->request->post['shipping_hitdhlexpress_address1'];
		} else {
			$data['shipping_hitdhlexpress_address1'] = $this->config->get('shipping_hitdhlexpress_address1');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_address2'])) {
			$data['shipping_hitdhlexpress_address2'] = $this->request->post['shipping_hitdhlexpress_address2'];
		} else {
			$data['shipping_hitdhlexpress_address2'] = $this->config->get('shipping_hitdhlexpress_address2');
		}
		
		
		if (isset($this->request->post['shipping_hitdhlexpress_city'])) {
			$data['shipping_hitdhlexpress_city'] = $this->request->post['shipping_hitdhlexpress_city'];
		} else {
			$data['shipping_hitdhlexpress_city'] = $this->config->get('shipping_hitdhlexpress_city');
		}
		
		
		if (isset($this->request->post['shipping_hitdhlexpress_state'])) {
			$data['shipping_hitdhlexpress_state'] = $this->request->post['shipping_hitdhlexpress_state'];
		} else {
			$data['shipping_hitdhlexpress_state'] = $this->config->get('shipping_hitdhlexpress_state');
		}
		
		
		if (isset($this->request->post['shipping_hitdhlexpress_country_code'])) {
			$data['shipping_hitdhlexpress_country_code'] = $this->request->post['shipping_hitdhlexpress_country_code'];
		} else {
			$data['shipping_hitdhlexpress_country_code'] = $this->config->get('shipping_hitdhlexpress_country_code');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_country_exclude'])) {
			$data['shipping_hitdhlexpress_country_exclude'] = $this->request->post['shipping_hitdhlexpress_country_exclude'];
		} else {//echo '<pre>';print_r($this->config->get('shipping_hitdhlexpress_country_exclude'));die();
			$data['shipping_hitdhlexpress_country_exclude'] = $this->config->get('shipping_hitdhlexpress_country_exclude');
		}

		$data['countrylist'] = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'CD' => 'Democratic Republic of the Congo',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TL' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'XK' => 'Kosovo',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'CG' => 'Republic of the Congo',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'VI' => 'U.S. Virgin Islands',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		
		if (isset($this->request->post['shipping_hitdhlexpress_postcode'])) {
			$data['shipping_hitdhlexpress_postcode'] = $this->request->post['shipping_hitdhlexpress_postcode'];
		} else {
			$data['shipping_hitdhlexpress_postcode'] = $this->config->get('shipping_hitdhlexpress_postcode');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_realtime_rates'])) {
			$data['shipping_hitdhlexpress_realtime_rates'] = $this->request->post['shipping_hitdhlexpress_realtime_rates'];
		} else {
			$data['shipping_hitdhlexpress_realtime_rates'] = $this->config->get('shipping_hitdhlexpress_realtime_rates');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_payment_country'])) {
			$data['shipping_hitdhlexpress_payment_country'] = $this->request->post['shipping_hitdhlexpress_payment_country'];
		} else {
			$data['shipping_hitdhlexpress_payment_country'] = $this->config->get('shipping_hitdhlexpress_payment_country');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_insurance'])) {
			$data['shipping_hitdhlexpress_insurance'] = $this->request->post['shipping_hitdhlexpress_insurance'];
		} else {
			$data['shipping_hitdhlexpress_insurance'] = $this->config->get('shipping_hitdhlexpress_insurance');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_display_time'])) {
			$data['shipping_hitdhlexpress_display_time'] = $this->request->post['shipping_hitdhlexpress_display_time'];
		} else {
			$data['shipping_hitdhlexpress_display_time'] = $this->config->get('shipping_hitdhlexpress_display_time');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_front_end_logs'])) {
			$data['shipping_hitdhlexpress_front_end_logs'] = $this->request->post['shipping_hitdhlexpress_front_end_logs'];
		} else {
			$data['shipping_hitdhlexpress_front_end_logs'] = $this->config->get('shipping_hitdhlexpress_front_end_logs');
		}
			
		if (isset($this->request->post['shipping_hitdhlexpress_rate_type'])) {
			$data['shipping_hitdhlexpress_rate_type'] = $this->request->post['shipping_hitdhlexpress_rate_type'];
		} else {
			$data['shipping_hitdhlexpress_rate_type'] = $this->config->get('shipping_hitdhlexpress_rate_type');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_pay_con'])) {
			$data['shipping_hitdhlexpress_pay_con'] = $this->request->post['shipping_hitdhlexpress_pay_con'];
		} else {
			$data['shipping_hitdhlexpress_pay_con'] = $this->config->get('shipping_hitdhlexpress_pay_con');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_cus_pay_con'])) {
			$data['shipping_hitdhlexpress_cus_pay_con'] = $this->request->post['shipping_hitdhlexpress_cus_pay_con'];
		} else {
			$data['shipping_hitdhlexpress_cus_pay_con'] = $this->config->get('shipping_hitdhlexpress_cus_pay_con');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_tax_class_id'])) {
			$data['shipping_hitdhlexpress_tax_class_id'] = $this->request->post['shipping_hitdhlexpress_tax_class_id'];
		} else {
			$data['shipping_hitdhlexpress_tax_class_id'] = $this->config->get('shipping_hitdhlexpress_tax_class_id');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_geo_zone_id'])) {
			$data['shipping_hitdhlexpress_geo_zone_id'] = $this->request->post['shipping_hitdhlexpress_geo_zone_id'];
		} else {
			$data['shipping_hitdhlexpress_geo_zone_id'] = $this->config->get('shipping_hitdhlexpress_geo_zone_id');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_service'])) {
			$data['shipping_hitdhlexpress_service'] = $this->request->post['shipping_hitdhlexpress_service'];
		} elseif ($this->config->has('shipping_hitdhlexpress_service')) {
			$data['shipping_hitdhlexpress_service'] = $this->config->get('shipping_hitdhlexpress_service');
		} else {
			$data['shipping_hitdhlexpress_service'] = array();
		}

		$data['services'] = array();

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_1'),
			'value' => '1'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_2'),
			'value' => '2'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_3'),
			'value' => '3'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_4'),
			'value' => '4'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_5'),
			'value' => '5'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_7'),
			'value' => '7'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_8'),
			'value' => '8'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_9'),
			'value' => '9'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_B'),
			'value' => 'B'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_C'),
			'value' => 'C'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_D'),
			'value' => 'D'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_E'),
			'value' => 'E'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_F'),
			'value' => 'F'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_G'),
			'value' => 'G'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_H'),
			'value' => 'H'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_I'),
			'value' => 'I'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_J'),
			'value' => 'J'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_K'),
			'value' => 'K'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_L'),
			'value' => 'L'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_M'),
			'value' => 'M'
		);

		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_N'),
			'value' => 'N'
		);
		
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_O'),
			'value' => 'O'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_P'),
			'value' => 'P'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_Q'),
			'value' => 'Q'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_R'),
			'value' => 'R'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_S'),
			'value' => 'S'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_T'),
			'value' => 'T'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_U'),
			'value' => 'U'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_V'),
			'value' => 'V'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_W'),
			'value' => 'W'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_X'),
			'value' => 'X'
		);
		$data['services'][] = array(
			'text'  => $this->language->get('text_dhl_Y'),
			'value' => 'y'
		);
		
		if (isset($this->request->post['shipping_hitdhlexpress_weight'])) {
			$data['shipping_hitdhlexpress_weight'] = $this->request->post['shipping_hitdhlexpress_weight'];
		} else {
			$data['shipping_hitdhlexpress_weight'] = $this->config->get('shipping_hitdhlexpress_weight');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_packing_type'])) {
			$data['shipping_hitdhlexpress_packing_type'] = $this->request->post['shipping_hitdhlexpress_packing_type'];
		} else {
			$data['shipping_hitdhlexpress_packing_type'] = $this->config->get('shipping_hitdhlexpress_packing_type');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_per_item'])) {
			$data['shipping_hitdhlexpress_per_item'] = $this->request->post['shipping_hitdhlexpress_per_item'];
		} else {
			$data['shipping_hitdhlexpress_per_item'] = $this->config->get('shipping_hitdhlexpress_per_item');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_show_etd'])) {
			$data['shipping_hitdhlexpress_show_etd'] = $this->request->post['shipping_hitdhlexpress_show_etd'];
		} else {
			$data['shipping_hitdhlexpress_show_etd'] = $this->config->get('shipping_hitdhlexpress_show_etd');
		}
			
		if (isset($this->request->post['shipping_hitdhlexpress_wight_b'])) {
			$data['shipping_hitdhlexpress_wight_b'] = $this->request->post['shipping_hitdhlexpress_wight_b'];
		} else {
			$data['shipping_hitdhlexpress_wight_b'] = $this->config->get('shipping_hitdhlexpress_wight_b');
		}
		
				
		if (isset($this->request->post['shipping_hitdhlexpress_weight_c'])) {
			$data['shipping_hitdhlexpress_weight_c'] = $this->request->post['shipping_hitdhlexpress_weight_c'];
		} else {
			$data['shipping_hitdhlexpress_weight_c'] = $this->config->get('shipping_hitdhlexpress_weight_c');
		}
		
				
		if (isset($this->request->post['shipping_hitdhlexpress_plt'])) {
			$data['shipping_hitdhlexpress_plt'] = $this->request->post['shipping_hitdhlexpress_plt'];
		} else {
			$data['shipping_hitdhlexpress_plt'] = $this->config->get('shipping_hitdhlexpress_plt');
		}
		
				
		if (isset($this->request->post['shipping_hitdhlexpress_sat'])) {
			$data['shipping_hitdhlexpress_sat'] = $this->request->post['shipping_hitdhlexpress_sat'];
		} else {
			$data['shipping_hitdhlexpress_sat'] = $this->config->get('shipping_hitdhlexpress_sat');
		}
		
				
		if (isset($this->request->post['shipping_hitdhlexpress_email_trach'])) {
			$data['shipping_hitdhlexpress_email_trach'] = $this->request->post['shipping_hitdhlexpress_email_trach'];
		} else {
			$data['shipping_hitdhlexpress_email_trach'] = $this->config->get('shipping_hitdhlexpress_email_trach');
		}
				
		if (isset($this->request->post['shipping_hitdhlexpress_airway'])) {
			$data['shipping_hitdhlexpress_airway'] = $this->request->post['shipping_hitdhlexpress_airway'];
		} else {
			$data['shipping_hitdhlexpress_airway'] = $this->config->get('shipping_hitdhlexpress_airway');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_hide_ship_box'])) {
			$data['shipping_hitdhlexpress_hide_ship_box'] = $this->request->post['shipping_hitdhlexpress_hide_ship_box'];
		} else {
			$data['shipping_hitdhlexpress_hide_ship_box'] = $this->config->get('shipping_hitdhlexpress_hide_ship_box');
		}
				
		if (isset($this->request->post['shipping_hitdhlexpress_dropoff_type'])) {
			$data['shipping_hitdhlexpress_dropoff_type'] = $this->request->post['shipping_hitdhlexpress_dropoff_type'];
		} else {
			$data['shipping_hitdhlexpress_dropoff_type'] = $this->config->get('shipping_hitdhlexpress_dropoff_type');
		}
				
		if (isset($this->request->post['shipping_hitdhlexpress_duty_type'])) {
			$data['shipping_hitdhlexpress_duty_type'] = $this->request->post['shipping_hitdhlexpress_duty_type'];
		} else {
			$data['shipping_hitdhlexpress_duty_type'] = $this->config->get('shipping_hitdhlexpress_duty_type');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_output_type'])) {
			$data['shipping_hitdhlexpress_output_type'] = $this->request->post['shipping_hitdhlexpress_output_type'];
		} else {
			$data['shipping_hitdhlexpress_output_type'] = $this->config->get('shipping_hitdhlexpress_output_type');
		}
		
		if (isset($this->request->post['shipping_hitdhlexpress_manuf_cntry_nme'])) {
			$data['shipping_hitdhlexpress_manuf_cntry_nme'] = $this->request->post['shipping_hitdhlexpress_manuf_cntry_nme'];
		} else {
			$data['shipping_hitdhlexpress_manuf_cntry_nme'] = $this->config->get('shipping_hitdhlexpress_manuf_cntry_nme');
		}

		if (isset($this->request->post['shipping_hitdhlexpress_shipment_content'])) {
			$data['shipping_hitdhlexpress_shipment_content'] = $this->request->post['shipping_hitdhlexpress_shipment_content'];
		} else {
			$data['shipping_hitdhlexpress_shipment_content'] = $this->config->get('shipping_hitdhlexpress_shipment_content');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_logo'])) {
			$data['shipping_hitdhlexpress_logo'] = $this->request->post['shipping_hitdhlexpress_logo'];
		} else {
			$data['shipping_hitdhlexpress_logo'] = $this->config->get('shipping_hitdhlexpress_logo');
		}
		
			if (isset($this->request->post['shipping_hitdhlexpress_picper'])) {
			$data['shipping_hitdhlexpress_picper'] = $this->request->post['shipping_hitdhlexpress_picper'];
		} else {
			$data['shipping_hitdhlexpress_picper'] = $this->config->get('shipping_hitdhlexpress_picper');
		}
			if (isset($this->request->post['shipping_hitdhlexpress_piccon'])) {
			$data['shipping_hitdhlexpress_piccon'] = $this->request->post['shipping_hitdhlexpress_piccon'];
		} else {
			$data['shipping_hitdhlexpress_piccon'] = $this->config->get('shipping_hitdhlexpress_piccon');
		}
			if (isset($this->request->post['shipping_hitdhlexpress_pickup_time'])) {
			$data['shipping_hitdhlexpress_pickup_time'] = $this->request->post['shipping_hitdhlexpress_pickup_time'];
		} else {
			$data['shipping_hitdhlexpress_pickup_time'] = $this->config->get('shipping_hitdhlexpress_pickup_time');
		}
			if (isset($this->request->post['shipping_hitdhlexpress_close_time'])) {
			$data['shipping_hitdhlexpress_close_time'] = $this->request->post['shipping_hitdhlexpress_close_time'];
		} else {
			$data['shipping_hitdhlexpress_close_time'] = $this->config->get('shipping_hitdhlexpress_close_time');
		}

		//licence
		if (isset($this->request->post['shipping_hitdhlexpress_licence_licence'])) {
			$data['shipping_hitdhlexpress_licence_licence'] = $this->request->post['shipping_hitdhlexpress_licence_licence'];
		} else {
			$data['shipping_hitdhlexpress_licence_licence'] = $this->config->get('shipping_hitdhlexpress_licence_licence');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_licence_email'])) {
			$data['shipping_hitdhlexpress_licence_email'] = $this->request->post['shipping_hitdhlexpress_licence_email'];
		} else {
			$data['shipping_hitdhlexpress_licence_email'] = $this->config->get('shipping_hitdhlexpress_licence_email');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_licence_expires'])) {
			$data['shipping_hitdhlexpress_licence_expires'] = $this->request->post['shipping_hitdhlexpress_licence_expires'];
		} else {
			$data['shipping_hitdhlexpress_licence_expires'] = $this->config->get('shipping_hitdhlexpress_licence_expires');
		}
		if (isset($this->request->post['shipping_hitdhlexpress_licence_status_licence'])) {
			$data['shipping_hitdhlexpress_licence_status_licence'] = $this->request->post['shipping_hitdhlexpress_licence_status_licence'];
		} else {
			$data['shipping_hitdhlexpress_licence_status_licence'] = $this->config->get('shipping_hitdhlexpress_licence_status_licence');
		}
		
		//renew 
		if (isset($this->request->post['shipping_hitdhlexpress_renew_expires'])) {
			$data['shipping_hitdhlexpress_renew_expires'] = $this->request->post['shipping_hitdhlexpress_renew_expires'];
		} else {
			$data['shipping_hitdhlexpress_renew_expires'] = $this->config->get('shipping_hitdhlexpress_renew_expires');
		}

		if($data['shipping_hitdhlexpress_licence_expires'])
		{
			$curdate=strtotime(date('d-m-Y',strtotime('now')));
			$expire_date=strtotime( date('d-m-Y', strtotime($data['shipping_hitdhlexpress_licence_expires']) ) );
			if($expire_date < $curdate)
			{
				if($data['shipping_hitdhlexpress_renew_expires'])
				{
					$expire_date = $expire_date=strtotime( date('d-m-Y', strtotime($data['shipping_hitdhlexpress_renew_expires']) ) );
					if($expire_date < $curdate)
					{
						$data['shipping_hitdhlexpress_renew_status_licence'] = 'Expired';
					}
				}else
				{
					$data['shipping_hitdhlexpress_renew_status_licence'] = 'Expired';
				}			
			}
			$data['shipping_hitdhlexpress_licence_expires'] = date('D-M-Y',$expire_date);

		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		//thilak
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/shipping/hitdhlexpress', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/hitdhlexpress')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['shipping_hitdhlexpress_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}

		if (!$this->request->post['shipping_hitdhlexpress_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (!$this->request->post['shipping_hitdhlexpress_account']) {
			$this->error['account'] = $this->language->get('error_account');
		}

		if (!$this->request->post['shipping_hitdhlexpress_postcode']) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		return !$this->error;
	}
}