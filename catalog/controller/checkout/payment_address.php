<?php

class ControllerCheckoutPaymentAddress extends Controller {
    public function index() {
        $this->load->language( 'checkout/checkout' );

        if ( isset( $this->session->data[ 'payment_address' ][ 'address_id' ] ) ) {
            $data[ 'address_id' ] = $this->session->data[ 'payment_address' ][ 'address_id' ];
        } else {
            $data[ 'address_id' ] = $this->customer->getAddressId();
        }

        $this->load->model( 'account/address' );

        $data[ 'addresses' ] = $this->model_account_address->getAddresses();

        if ( isset( $this->session->data[ 'payment_address' ][ 'country_id' ] ) ) {
            $data[ 'country_id' ] = $this->session->data[ 'payment_address' ][ 'country_id' ];
        } else {
            $data[ 'country_id' ] = $this->config->get( 'config_country_id' );
        }

        if ( isset( $this->session->data[ 'payment_address' ][ 'zone_id' ] ) ) {
            $data[ 'zone_id' ] = $this->session->data[ 'payment_address' ][ 'zone_id' ];
        } else {
            $data[ 'zone_id' ] = '';
        }

        $this->load->model( 'localisation/country' );

        $data[ 'countries' ] = $this->model_localisation_country->getCountries();

        // Custom Fields
        $data[ 'custom_fields' ] = array();

        $this->load->model( 'account/custom_field' );

        $custom_fields = $this->model_account_custom_field->getCustomFields( $this->config->get( 'config_customer_group_id' ) );

        foreach ( $custom_fields as $custom_field ) {
            if ( $custom_field[ 'location' ] == 'address' ) {
                $data[ 'custom_fields' ][] = $custom_field;
            }
        }

        if ( isset( $this->session->data[ 'payment_address' ][ 'custom_field' ] ) ) {
            $data[ 'payment_address_custom_field' ] = $this->session->data[ 'payment_address' ][ 'custom_field' ];
        } else {
            $data[ 'payment_address_custom_field' ] = array();
        }

		//print_r($data[ 'addresses' ]); die();
		//print_r($this->customer->getTelephone()); die();
        //die();
		for($i = 1; $i <= 20; $i++)
		  if($data[ 'addresses' ][ $i ])
			 $adress = $data[ 'addresses' ][ $i ];
		  
			 //print_r($adress); die();

		$address1 = $adress[ 'address_1' ] . ',' . $adress[ 'city' ] . ',' . $adress[ 'country' ];
		//echo $address1; die();
		$address2 = $adress[ 'postcode' ];
		//echo $address2; die();
		
		//print_r($this->getCoordinatesFromAddress( $address1 )); die();

        //print_r( $this->getCoordinatesFromAddress( $address2 ) );
        //die();

        $data[ 'distance' ] = $this->calculateDistance( $address1, $address2 );

        if ( $data[ 'distance' ] != null && $data[ 'distance' ] <= FREE_SHIPPING_KM )
        $data[ 'notice_shipping' ] = str_replace( '%d', $data[ 'distance' ], $this->language->get( 'notice_shipping_yes' ) );
        else if($data[ 'distance' ] != null && $data[ 'distance' ] > FREE_SHIPPING_KM)
		$data[ 'notice_shipping' ] = str_replace( '%d', $data[ 'distance' ], $this->language->get( 'notice_shipping_no' ) );
		else if($data[ 'distance' ] == 0)  
		$data[ 'notice_shipping' ] = str_replace( '%d', 0, $this->language->get( 'notice_shipping_yes' ) );

		if ( $this->getCoordinatesFromAddress( $address1 ) == null ) {
            $data[ 'km_address' ] = $this->language->get( 'km_address_no' );
            $data[ 'notice_shipping' ] =  $this->language->get( 'notice_shipping_invalid' );
        } else
        $data[ 'km_address' ] = $this->language->get( 'km_address_yes' );

        if ( $this->getCoordinatesFromAddress( $address2 ) == null ) {
            $data[ 'km_zipcode' ] = $this->language->get( 'km_zipcode_no' );
            $data[ 'notice_shipping' ] =  $this->language->get( 'notice_shipping_invalid' );
        } else
		$data[ 'km_zipcode' ] = $this->language->get( 'km_zipcode_yes' );
		
		$data['phone'] = $this->customer->getTelephone();
		$data['email'] = $this->customer->getEmail();
		  
       $this->response->setOutput( $this->load->view( 'checkout/payment_address', $data ) );
    }

    public function getCoordinatesFromAddress( $address ) {
        //echo $address;
        //die();
        $apiKey = API_KEY;
        // Replace with your Google Maps API key

        $address = urlencode( $address );

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

        $response = file_get_contents( $url );
        $data = json_decode( $response, true );

        //print_r( $data );
        //die();

        if ( $data[ 'status' ] == 'ZERO_RESULTS' )
        return null;

        if ( $data[ 'status' ] === 'OK' && isset( $data[ 'results' ][ 0 ][ 'geometry' ][ 'location' ] ) ) {
            return $data[ 'results' ][ 0 ][ 'geometry' ][ 'location' ];
        } else {
            return null;
        }
    }

    public function calculateDistance( $address1, $address2 )
    {
        $coords1 = $this->getCoordinatesFromAddress( $address1 );
        $coords2 = $this->getCoordinatesFromAddress( $address2 );

        if ( $coords1 && $coords2 ) {
            $earthRadius = 6371;
            // Earth's radius in kilometers

			$lat1 = deg2rad($coords1['lat']);
			$lon1 = deg2rad($coords1['lng']);
			$lat2 = deg2rad($coords2['lat']);
			$lon2 = deg2rad($coords2['lng']);

			$deltaLat = $lat2 - $lat1;
			$deltaLon = $lon2 - $lon1;

			$a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1) * cos($lat2) * sin($deltaLon / 2) * sin($deltaLon / 2);
			$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

			$distance = $earthRadius * $c;

			return round($distance); // Distance in kilometers
		} else {
			return null;
		}
    }

	public function uploadFile() {
		$file = ($_FILES["file_1"]["name"] != '') ? $_FILES["file_1"] : $_FILES["file_2"];

		$targetDirectory = "uploads/"; // Directory where uploaded files will be saved
		$targetFile = $targetDirectory . basename($file["name"]); // Get the file name
		
		// Try to upload the file
			if (move_uploaded_file($file["tmp_name"], $targetFile)) {
				//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
				$this->session->data['upload_file'] = $file["name"];
				//$_SESSION['upload_file'] = 'naddd'; //$_FILES["file_1"]["tmp_name"];
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
	}

	public function save() {
		$this->load->language('checkout/checkout');

		//$this->uploadFile();

		$json = array();

		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			//$json['redirect'] = $this->url->link('checkout/cart');
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
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!$json) {
			$this->load->model('account/address');
							
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'existing') {
				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->language->get('error_address');
				}

				if (!$json) {
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->post['address_id']);

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			} else {
				if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
					$json['error']['firstname'] = $this->language->get('error_firstname');
				}

				if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
					$json['error']['lastname'] = $this->language->get('error_lastname');
				}

				if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
					$json['error']['address_1'] = $this->language->get('error_address_1');
				}

				if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
					$json['error']['city'] = $this->language->get('error_city');
				}

				$this->load->model('localisation/country');

				$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

				if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
					$json['error']['postcode'] = $this->language->get('error_postcode');
				}

				if ($this->request->post['country_id'] == '') {
					$json['error']['country'] = $this->language->get('error_country');
				}

				if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
					$json['error']['zone'] = $this->language->get('error_zone');
				}

				// Custom field validation
				$this->load->model('account/custom_field');

				$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

				foreach ($custom_fields as $custom_field) {
					if ($custom_field['location'] == 'address') {
						if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
							$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
						} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
							$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
						}
					}
				}

				if (!$json) {
					$address_id = $this->model_account_address->addAddress($this->customer->getId(), $this->request->post);

					$this->session->data['payment_address'] = $this->model_account_address->getAddress($address_id);

					// If no default address ID set we use the last address
					if (!$this->customer->getAddressId()) {
						$this->load->model('account/customer');
						
						$this->model_account_customer->editAddressId($this->customer->getId(), $address_id);
					}

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json' );
            $this->response->setOutput( json_encode( $json ) );
        }
    }