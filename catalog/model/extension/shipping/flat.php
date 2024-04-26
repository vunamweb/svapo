<?php

class ModelExtensionShippingFlat extends Model {
    function getQuote( $address ) {
        //print_r($address); die();

        $this->load->language( 'extension/shipping/flat' );

        $check = false;

        $address1 = $address[ 'address_1' ] . ',' . $address[ 'city' ] . ',' . $address[ 'country' ];
        $address2 = $address[ 'postcode' ];

        //echo $address2 ; die();

        //print_r($this->getCoordinatesFromAddress( $address2 )); die();

        /* $distance = $this->calculateDistance( $address1, $address2 );

        if ( $this->getCoordinatesFromAddress( $address1 ) == null || $this->getCoordinatesFromAddress( $address2 ) == null )
            $title =  $this->language->get( 'notice_shipping_invalid' );
        else if ( $distance <= FREE_SHIPPING_KM ) {
            $check = true;

            $title = str_replace('%d', $distance, $this->language->get( 'notice_shipping_yes' ));
        } else {
            $title = str_replace('%d', $distance, $this->language->get( 'notice_shipping_no' ));
        } */
        
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . ( int )$this->config->get( 'shipping_flat_geo_zone_id' ) . "' AND country_id = '" . ( int )$address[ 'country_id' ] . "' AND (zone_id = '" . ( int )$address[ 'zone_id' ] . "' OR zone_id = '0')" );

        if ( !$this->config->get( 'shipping_flat_geo_zone_id' ) ) {
            $status = true;
        } elseif ( $query->num_rows ) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        $cost = ( $check ) ? 0 : $this->config->get( 'shipping_flat_cost' );

        if ( $status ) {
            $quote_data = array();

            $quote_data[ 'flat' ] = array(
                'code'         => 'flat.flat',
                'title'        => $this->language->get( 'text_description' ),
                'cost'         => $cost,
                'tax_class_id' => $this->config->get( 'shipping_flat_tax_class_id' ),
                'text'         => $this->currency->format( $this->tax->calculate( $cost, $this->config->get( 'shipping_flat_tax_class_id' ), $this->config->get( 'config_tax' ) ), $this->session->data[ 'currency' ] )
            );

            $method_data = array(
                'code'       => 'flat',
                'title'      => $this->language->get( 'text_title' ),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get( 'shipping_flat_sort_order' ),
                'error'      => false
            );
        }

        return $method_data;
    }

    public function calculateDistance( $address1, $address2 ) {
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
}