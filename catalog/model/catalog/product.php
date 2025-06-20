<?php

class ModelCatalogProduct extends Model {
    public function updateViewed( $product_id ) {
        $this->db->query( 'UPDATE ' . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . ( int )$product_id . "'" );
    }

    public function getListNameAttributeProduct( $attributes ) {
        $list = array();

        $listAttribute = array();

        $attributes = json_decode( $attributes );

        foreach ( $attributes as $attribute ) {
            $query = $this->db->query( 'SELECT ad.name, ad.image, agd.name as name1 FROM ' . DB_PREFIX . "attribute_description ad, ".DB_PREFIX."attribute a, ".DB_PREFIX."attribute_group_description agd WHERE a.attribute_id = ad.attribute_id and a.attribute_group_id = agd.attribute_group_id and ad. attribute_id  = '" . ( int )$attribute . "'" );

            $result = $query->row;

            $array = array();

            $array[ 'name' ] = $result[ 'name' ];
            $array[ 'image' ] = $result[ 'image' ];
            $array['name1'] = $result['name1'];

            $list[] = $array;
        }

        foreach($list as $item) {
            $nameGroup = $item['name1'];
            $nameAttribute = $item['name'];
            $image = $item['image'];

            $position = $this->getPositionAttribute($nameGroup, $listAttribute);

            if($position == -1) {
                $countListAttribute = count($listAttribute);

                $array = array();
                $array['name'] = $nameAttribute;
                $array['image'] = $image; 

                $listAttribute[$countListAttribute]['name'] = $nameGroup;
                $listAttribute[$countListAttribute]['list'][0] = $array; 
            } else {
                $array = array();
                $array['name'] = $nameAttribute;
                $array['image'] = $image; 

                $listAttribute[$position]['name'] = $nameGroup;
                $listAttribute[$countListAttribute]['list'][] = $array; 
            }
        }

        //print_r($listAttribute); die();

        return $listAttribute;
    }

    public function getListNameAttributeProductInCategory( $attributes ) {
        $list = array();

        $listAttribute = array();

        $attributes = json_decode( $attributes );

        foreach ( $attributes as $attribute ) {
            $query = $this->db->query( 'SELECT ad.name, ad.image, agd.name as name1 FROM ' . DB_PREFIX . "attribute_description ad, ".DB_PREFIX."attribute a, ".DB_PREFIX."attribute_group_description agd WHERE a.attribute_id = ad.attribute_id and a.attribute_group_id = agd.attribute_group_id and (agd.attribute_group_id = 11 or agd.attribute_group_id = 9) and ad.attribute_id  = '" . ( int )$attribute . "'" );

            $result = $query->row;

            $array = array();

            $array[ 'name' ] = $result[ 'name' ];
            $array[ 'image' ] = $result[ 'image' ];
            $array['name1'] = $result['name1'];

            $list[] = $array;
        }

        foreach($list as $item) {
            $nameGroup = $item['name1'];
            $nameAttribute = $item['name'];
            $image = $item['image'];

            $position = $this->getPositionAttribute($nameGroup, $listAttribute);

            if($position == -1) {
                $countListAttribute = count($listAttribute);

                $array = array();
                $array['name'] = $nameAttribute;
                $array['image'] = $image; 

                $listAttribute[$countListAttribute]['name'] = $nameGroup;
                $listAttribute[$countListAttribute]['list'][0] = $array; 
            } else {
                $array = array();
                $array['name'] = $nameAttribute;
                $array['image'] = $image; 

                $listAttribute[$position]['name'] = $nameGroup;
                $listAttribute[$countListAttribute]['list'][] = $array; 
            }
        }

        //print_r($listAttribute); die();

        return $listAttribute;
    }

    public function getValueKluivarOfproduct($product_id) {
        $sql = 'select text from '.DB_PREFIX.'product_attribute where product_id = '.$product_id.' and (attribute_id = 33 )';

        $query = $this->db->query($sql);

        return ($query->row['text'] != '') ? $query->row['text'] : 'Keine Daten';
    }

    public function getValueTHCOfproduct($product_id) {
        $sql = 'select text from '.DB_PREFIX.'product_attribute where product_id = '.$product_id.' and (attribute_id = 34 )';

        $query = $this->db->query($sql);

        return ($query->row['text'] != '') ? $query->row['text'] : 'Keine Daten';
    }
	
	public function getValueCBDOfproduct($product_id) {
		$sql = 'select text from '.DB_PREFIX.'product_attribute where product_id = '.$product_id.' and (attribute_id = 35 )';
	
		$query = $this->db->query($sql);
	
		return ($query->row['text'] != '') ? $query->row['text'] : 'Keine Daten';
	}
	
	public function getValueBehandlungOfproduct($product_id) {
		$sql = 'select text from '.DB_PREFIX.'product_attribute where product_id = '.$product_id.' and (attribute_id = 105 )';
	
		$query = $this->db->query($sql);
	
		return ($query->row['text'] != '') ? $query->row['text'] : 'Keine Daten';
	}

	public function getValueCountryOfproduct($product_id) {
		$sql = 'select text from '.DB_PREFIX.'product_attribute where product_id = '.$product_id.' and (attribute_id = 42 )';
	
		$query = $this->db->query($sql);
	
		return ($query->row['text'] != '') ? $query->row['text'] : 'Keine Daten';
	}


    public function checkActivateProduct($ansay_id) {
        $sql = 'select status from '.DB_PREFIX.'product where mpn = "'.$ansay_id.'"';

        $query = $this->db->query($sql);

        $status = $query->row['status'];

        //echo $status . '/ddd'; die();
        return ($status == '') ? 1 :$status;
    }

    public function checkExistListProducts($listProduct, $key, $parentKey, $mcos = false) {
        $rawData = file_get_contents("php://input");
        $dataJSON = json_decode($rawData);

        $nameField = (!$mcos) ? 'mpn ' : 'isbn ';

        $result = array();

        if(!$mcos)
          $sql = 'select mpn from '.DB_PREFIX.'product';
        else
          $sql = 'select isbn from '.DB_PREFIX.'product';
         
        $query = $this->db->query($sql);

        foreach ( $query->rows as $row ) {
            $result[] = (!$mcos) ? $row['mpn'] : $row['isbn'];
        }

        foreach($listProduct as $product) 
          if(!isset($product[$key]) || $product[$key] == '') {
            echo json_encode(['error_codes' => 402, 'error' => "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key)]);
            ($key == 'id') ? $this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key)) : $this->document->writeLog($rawData, "Missing or invalid attribute: " . ($parentKey ? "$parentKey.$key" : $key), true, true);
					
            return false;				
          } else if(!$this->checkActivateProduct($product[$key])) {
            $errorLog = $product[$key] . ' is not activated, Please active this product in admin' . '<br>' . PHP_EOL;
            $errorLog .= $dataJSON->customer->firstname . ' ' . $dataJSON->customer->lastname;

            ($key == 'id') ? $this->document->writeLog($rawData, $errorLog) : $this->document->writeLog($rawData, $errorLog, true, true);
            echo json_encode(['error_codes' => 402, 'error' => $errorLog]);

            return false;
          }
          else if (!in_array($product[$key], $result)) {
            //echo json_encode(['error_codes' => 402, 'error' => ($parentKey ? "$parentKey.$key " . "$product[$key]" . " not exist" : $key)]);
            echo json_encode(['error_codes' => 402, 'error' => ($parentKey ? $nameField . "$product[$key]" . " not exist" : $key)]);

            try {
                //print_r($dataJSON); die();

                $errorLog = $product[$key] . ' = ' . $product['name'] . '<br>' . PHP_EOL;
                $errorLog .= $dataJSON->customer->firstname . ' ' . $dataJSON->customer->lastname;

                ($key == 'id') ? $this->document->writeLog($rawData, $errorLog) : $this->document->writeLog($rawData, $errorLog, true, true);
            } catch (\Exception $e) {
                ($key == 'id') ? $this->document->writeLog($rawData, ($parentKey ? $nameField . "$product[$key]" . " not exist" : $key)) : $this->document->writeLog($rawData, ($parentKey ? $nameField . "$product[$key]" . " not exist" : $key), true, true);
            } catch (\Throwable $e) {
                ($key == 'id') ? $this->document->writeLog($rawData, ($parentKey ? $nameField . "$product[$key]" . " not exist" : $key)) : $this->document->writeLog($rawData, ($parentKey ? $nameField . "$product[$key]" . " not exist" : $key), true, true);
            }

            return false;				
          }

          return true;
    }

    public function getIdFromSku($sku) {
        $sql = 'select product_id from '.DB_PREFIX.'product where sku = "'.$sku.'"';

        $query = $this->db->query($sql);

        //echo $query->row['product_id'] . '/';

        return $query->row['product_id'];
    }
    
    public function getIdFromMpn($mpn) {
        $sql = 'select product_id from '.DB_PREFIX.'product where mpn = "'.$mpn.'"';
    
        $query = $this->db->query($sql);
    
        //echo $query->row['product_id'] . '/';
    
        return ($query->row['product_id'] != '') ? $query->row['product_id'] : $this->getIdFromIsbn($mpn);
    }

    public function getIdFromIsbn($isbn) {
        $sql = 'select product_id from '.DB_PREFIX.'product where isbn = "'.$isbn.'"';
    
        $query = $this->db->query($sql);
    
        //echo $query->row['product_id'] . '/';
    
        return $query->row['product_id'];
    }

    public function getPositionAttribute($nameAttribute, $listAtrtibute) {
        for($i = 0; $i < count($listAtrtibute); $i++)
         if($listAtrtibute[$i]['name'] == $nameAttribute)
           return $i;

        return -1;   
    }

    public function getProduct( $product_id ) {
        $query = $this->db->query( 'SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM ' . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . 'product p LEFT JOIN ' . DB_PREFIX . 'product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ' . DB_PREFIX . 'product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN ' . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . ( int )$product_id . "' AND pd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "'" );

        if ( $query->num_rows ) {
            return array(
                'product_id'       => $query->row[ 'product_id' ],
                'name'             => $query->row[ 'name' ],
                'description'      => $query->row[ 'description' ],
                'meta_title'       => $query->row[ 'meta_title' ],
                'meta_description' => $query->row[ 'meta_description' ],
                'meta_keyword'     => $query->row[ 'meta_keyword' ],
                'tag'              => $query->row[ 'tag' ],
                'model'            => $query->row[ 'model' ],
                'sku'              => $query->row[ 'sku' ],
                'upc'              => $query->row[ 'upc' ],
                'ean'              => $query->row[ 'ean' ],
                'jan'              => $query->row[ 'jan' ],
                'isbn'             => $query->row[ 'isbn' ],
                'mpn'              => $query->row[ 'mpn' ],
                'location'         => $query->row[ 'location' ],
                'quantity'         => $query->row[ 'quantity' ],
                'stock_status'     => $query->row[ 'stock_status' ],
                'image'            => $query->row[ 'image' ],
                'manufacturer_id'  => $query->row[ 'manufacturer_id' ],
                'manufacturer'     => $query->row[ 'manufacturer' ],
                'attributes'       => $query->row[ 'attributes' ],
                'price'            => ( $query->row[ 'discount' ] ? $query->row[ 'discount' ] : $query->row[ 'price' ] ),
                'special'          => $query->row[ 'special' ],
                'reward'           => $query->row[ 'reward' ],
                'points'           => $query->row[ 'points' ],
                'tax_class_id'     => $query->row[ 'tax_class_id' ],
                'date_available'   => $query->row[ 'date_available' ],
                'weight'           => $query->row[ 'weight' ],
                'weight_class_id'  => $query->row[ 'weight_class_id' ],
                'length'           => $query->row[ 'length' ],
                'width'            => $query->row[ 'width' ],
                'height'           => $query->row[ 'height' ],
                'length_class_id'  => $query->row[ 'length_class_id' ],
                'subtract'         => $query->row[ 'subtract' ],
                'rating'           => round( ( $query->row[ 'rating' ] === null ) ? 0 : $query->row[ 'rating' ] ),
                'reviews'          => $query->row[ 'reviews' ] ? $query->row[ 'reviews' ] : 0,
                'minimum'          => $query->row[ 'minimum' ],
                'sort_order'       => $query->row[ 'sort_order' ],
                'status'           => $query->row[ 'status' ],
                'stock_status_id'  => $query->row[ 'stock_status_id' ],                
                'date_added'       => $query->row[ 'date_added' ],
                'date_modified'    => $query->row[ 'date_modified' ],
                'viewed'           => $query->row[ 'viewed' ]
            );
        } else {
            return false;
        }
    }

    public function getAtbOfGenetik($attribute_id) {
        foreach($attribute_id as $item)
          if(str_contains($item, '_radio'))
            return str_replace('_radio', '', $item);

        return null;    
    }

    public function getProductByAttribute( $results, $attribute_id, $manufacter_id ) {
        //print_r($results);
        $response = array();
        $resultsNew = array();
        
        $attribute_id = explode(',', $attribute_id);
        // get attribute of genetik
        $atbOfGenetik = $this->getAtbOfGenetik($attribute_id);

        $manufacter_list_id = explode(',', $manufacter_id);

        //print_r($manufacter_list_id); die();

        //print_r($results[50]); die();

        // if has filter of radio genetik, then just get result of atribute of genetik
        if($atbOfGenetik) {
            foreach ( $results as $result ) {
                $attributes = $result[ 'attributes' ];

                try {
                    $attributes = json_decode( $attributes );
    
                    if($attributes == '' || $attributes == null)
                      $attributes = array();
                } catch( Exception $e ) {
                    $attributes = array();
                }

                if(in_array($atbOfGenetik, $attributes))
                 $resultsNew[] = $result;
            } 
        } else { // if not has filter of radio genetik
            $resultsNew = $results;
        }

        //print_r($resultsNew); die();
          
        //echo $atbOfGenetik . '/' . count($attribute_id); die();
        foreach ( $resultsNew as $result ) {
            $check = false;

            $attributes = $result[ 'attributes' ];
            $product_manufacter_id = $result['manufacturer_id'];

            try {
                $attributes = json_decode( $attributes );

                if($attributes == '' || $attributes == null)
                  $attributes = array();
            } catch( Exception $e ) {
                $attributes = array();
            }

            //if($result['product_id'] == 50)
            //print_r($result[50]['attributes']); die();

            //print_r($attributes); die();
            foreach($attribute_id as $id)
              // if result contains atribute of filter or just filter of genetik
              if($id != '' && ! str_contains($id, '_radio') && in_array($id, $attributes) || ($atbOfGenetik && count($attribute_id) == 2))
                $check = true;
                
              if($manufacter_id != '' && in_array($product_manufacter_id, $manufacter_list_id))
                $check = true;    

            //if(true)
            if ($check)
            $response[] = $result;
        }

        //print_r($attribute_id); die();

        return $response;
    }

    public function getProducts( $data = array() ) {
        $sql = 'SELECT p.product_id, (SELECT AVG(rating) AS total FROM ' . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

        if ( !empty( $data[ 'filter_category_id' ] ) ) {
            if ( !empty( $data[ 'filter_sub_category' ] ) ) {
                $sql .= ' FROM ' . DB_PREFIX . 'category_path cp LEFT JOIN ' . DB_PREFIX . 'product_to_category p2c ON (cp.category_id = p2c.category_id)';
            } else {
                $sql .= ' FROM ' . DB_PREFIX . 'product_to_category p2c';
            }

            if ( !empty( $data[ 'filter_filter' ] ) ) {
                $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN ' . DB_PREFIX . 'product p ON (pf.product_id = p.product_id)';
            } else {
                $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product p ON (p2c.product_id = p.product_id)';
            }
        } else {
            $sql .= ' FROM ' . DB_PREFIX . 'product p';
        }

        $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "'";

        if ( !empty( $data[ 'filter_category_id' ] ) ) {
            if ( !empty( $data[ 'filter_sub_category' ] ) ) {
                $sql .= " AND cp.path_id = '" . ( int )$data[ 'filter_category_id' ] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . ( int )$data[ 'filter_category_id' ] . "'";
            }

            if ( !empty( $data[ 'filter_filter' ] ) ) {
                $implode = array();

                $filters = explode( ',', $data[ 'filter_filter' ] );

                foreach ( $filters as $filter_id ) {
                    $implode[] = ( int )$filter_id;
                }

                $sql .= ' AND pf.filter_id IN (' . implode( ',', $implode ) . ')';
            }
        }

        if ( !empty( $data[ 'filter_name' ] ) || !empty( $data[ 'filter_tag' ] ) ) {
            $sql .= ' AND (';

            if ( !empty( $data[ 'filter_name' ] ) ) {
                $implode = array();

                $words = explode( ' ', trim( preg_replace( '/\s+/', ' ', $data[ 'filter_name' ] ) ) );

                foreach ( $words as $word ) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape( $word ) . "%'";
                }

                if ( $implode ) {
                    $sql .= ' ' . implode( ' AND ', $implode ) . '';
                }

                if ( !empty( $data[ 'filter_description' ] ) ) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape( $data[ 'filter_name' ] ) . "%'";
                }
            }

            if ( !empty( $data[ 'filter_name' ] ) && !empty( $data[ 'filter_tag' ] ) ) {
                $sql .= ' OR ';
            }

            if ( !empty( $data[ 'filter_tag' ] ) ) {
                $implode = array();

                $words = explode( ' ', trim( preg_replace( '/\s+/', ' ', $data[ 'filter_tag' ] ) ) );

                foreach ( $words as $word ) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape( $word ) . "%'";
                }

                if ( $implode ) {
                    $sql .= ' ' . implode( ' AND ', $implode ) . '';
                }
            }

            if ( !empty( $data[ 'filter_name' ] ) ) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
            }

            $sql .= ')';
        }

        if ( !empty( $data[ 'filter_manufacturer_id' ] ) ) {
            $sql .= " AND p.manufacturer_id = '" . ( int )$data[ 'filter_manufacturer_id' ] . "'";
        }

        $sql .= ' GROUP BY p.product_id';

        $sort_data = array(
            'pd.name',
            'p.date_added',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added',
			'p.thc'
        );

        if ( isset( $data[ 'sort' ] ) && in_array( $data[ 'sort' ], $sort_data ) ) {
            if ( $data[ 'sort' ] == 'pd.name' || $data[ 'sort' ] == 'p.model' || $data[ 'sort' ] == 'p.date' ) {
                $sql .= ' ORDER BY LCASE(' . $data[ 'sort' ] . ')';
            } elseif ( $data[ 'sort' ] == 'p.price' ) {
                $sql .= ' ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)';
            } else {
                $sql .= ' ORDER BY ' . $data[ 'sort' ];
            }
        } else {
            $sql .= ' ORDER BY p.sort_order';
        }

        if ( isset( $data[ 'order' ] ) && ( $data[ 'order' ] == 'DESC' ) ) {
            $sql .= ' DESC, LCASE(pd.name) DESC';
        } else {
            $sql .= ' ASC, LCASE(pd.name) ASC';
        }

        if ( isset( $data[ 'start' ] ) || isset( $data[ 'limit' ] ) ) {
            if ( $data[ 'start' ] < 0 ) {
                $data[ 'start' ] = 0;
            }

            if ( $data[ 'limit' ] < 1 ) {
                $data[ 'limit' ] = 20;
            }

            $sql .= ' LIMIT ' . ( int )$data[ 'start' ] . ',' . ( int )$data[ 'limit' ];
        }

        $product_data = array();

        //echo $sql; die();

        
        $query = $this->db->query( $sql );

        foreach ( $query->rows as $result ) {
            $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
        }

        return $product_data;
    }

    public function getProductSpecials( $data = array() ) {
        $sql = 'SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM ' . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . 'product_special ps LEFT JOIN ' . DB_PREFIX . 'product p ON (ps.product_id = p.product_id) LEFT JOIN ' . DB_PREFIX . 'product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "' AND ps.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'ps.price',
            'rating',
            'p.sort_order'
        );

        if ( isset( $data[ 'sort' ] ) && in_array( $data[ 'sort' ], $sort_data ) ) {
            if ( $data[ 'sort' ] == 'pd.name' || $data[ 'sort' ] == 'p.model' ) {
                $sql .= ' ORDER BY LCASE(' . $data[ 'sort' ] . ')';
            } else {
                $sql .= ' ORDER BY ' . $data[ 'sort' ];
            }
        } else {
            $sql .= ' ORDER BY p.sort_order';
        }

        if ( isset( $data[ 'order' ] ) && ( $data[ 'order' ] == 'DESC' ) ) {
            $sql .= ' DESC, LCASE(pd.name) DESC';
        } else {
            $sql .= ' ASC, LCASE(pd.name) ASC';
        }

        if ( isset( $data[ 'start' ] ) || isset( $data[ 'limit' ] ) ) {
            if ( $data[ 'start' ] < 0 ) {
                $data[ 'start' ] = 0;
            }

            if ( $data[ 'limit' ] < 1 ) {
                $data[ 'limit' ] = 20;
            }

            $sql .= ' LIMIT ' . ( int )$data[ 'start' ] . ',' . ( int )$data[ 'limit' ];
        }

        $product_data = array();

        $query = $this->db->query( $sql );

        foreach ( $query->rows as $result ) {
            $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
        }

        return $product_data;
    }

    public function getLatestProducts( $limit ) {
        $product_data = $this->cache->get( 'product.latest.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit );

        if ( !$product_data ) {
            $product_data = array();
            $query = $this->db->query( 'SELECT p.product_id FROM ' . DB_PREFIX . 'product p LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "' ORDER BY p.date_added DESC LIMIT " . ( int )$limit );

            foreach ( $query->rows as $result ) {
                $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
            }

            $this->cache->set( 'product.latest.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit, $product_data );
        }

        return $product_data;
    }

    public function getPopularProducts( $limit ) {
        $product_data = $this->cache->get( 'product.popular.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit );

        if ( !$product_data ) {
            $product_data = array();
            $query = $this->db->query( 'SELECT p.product_id FROM ' . DB_PREFIX . 'product p LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . ( int )$limit );

            foreach ( $query->rows as $result ) {
                $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
            }

            $this->cache->set( 'product.popular.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit, $product_data );
        }

        return $product_data;
    }

    public function getBestSellerProducts( $limit ) {
        $product_data = $this->cache->get( 'product.bestseller.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit );

        if ( !$product_data ) {
            $product_data = array();

            $query = $this->db->query( 'SELECT op.product_id, SUM(op.quantity) AS total FROM ' . DB_PREFIX . 'order_product op LEFT JOIN `' . DB_PREFIX . 'order` o ON (op.order_id = o.order_id) LEFT JOIN `' . DB_PREFIX . 'product` p ON (op.product_id = p.product_id) LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . ( int )$limit );

            foreach ( $query->rows as $result ) {
                $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
            }

            $this->cache->set( 'product.bestseller.' . ( int )$this->config->get( 'config_language_id' ) . '.' . ( int )$this->config->get( 'config_store_id' ) . '.' . $this->config->get( 'config_customer_group_id' ) . '.' . ( int )$limit, $product_data );
        }

        return $product_data;
    }

    public function getProductAttributes( $product_id ) {
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->db->query( 'SELECT ag.attribute_group_id, agd.name FROM ' . DB_PREFIX . 'product_attribute pa LEFT JOIN ' . DB_PREFIX . 'attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN ' . DB_PREFIX . 'attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN ' . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . ( int )$product_id . "' AND agd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name" );

        foreach ( $product_attribute_group_query->rows as $product_attribute_group ) {
            $product_attribute_data = array();

            $product_attribute_query = $this->db->query( 'SELECT a.attribute_id, ad.name, pa.text FROM ' . DB_PREFIX . 'product_attribute pa LEFT JOIN ' . DB_PREFIX . 'attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN ' . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . ( int )$product_id . "' AND a.attribute_group_id = '" . ( int )$product_attribute_group[ 'attribute_group_id' ] . "' AND ad.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' AND pa.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' ORDER BY a.sort_order, ad.name" );

            foreach ( $product_attribute_query->rows as $product_attribute ) {
                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute[ 'attribute_id' ],
                    'name'         => $product_attribute[ 'name' ],
                    'text'         => $product_attribute[ 'text' ]
                );
            }

            $product_attribute_group_data[] = array(
                'attribute_group_id' => $product_attribute_group[ 'attribute_group_id' ],
                'name'               => $product_attribute_group[ 'name' ],
                'attribute'          => $product_attribute_data
            );
        }

        return $product_attribute_group_data;
    }

    public function getProductOptions( $product_id ) {
        $product_option_data = array();

        $product_option_query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . 'product_option po LEFT JOIN `' . DB_PREFIX . 'option` o ON (po.option_id = o.option_id) LEFT JOIN ' . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . ( int )$product_id . "' AND od.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' ORDER BY o.sort_order" );

        foreach ( $product_option_query->rows as $product_option ) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . 'product_option_value pov LEFT JOIN ' . DB_PREFIX . 'option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN ' . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . ( int )$product_id . "' AND pov.product_option_id = '" . ( int )$product_option[ 'product_option_id' ] . "' AND ovd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' ORDER BY ov.sort_order" );

            foreach ( $product_option_value_query->rows as $product_option_value ) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value[ 'product_option_value_id' ],
                    'option_value_id'         => $product_option_value[ 'option_value_id' ],
                    'name'                    => $product_option_value[ 'name' ],
                    'image'                   => $product_option_value[ 'image' ],
                    'quantity'                => $product_option_value[ 'quantity' ],
                    'subtract'                => $product_option_value[ 'subtract' ],
                    'price'                   => $product_option_value[ 'price' ],
                    'price_prefix'            => $product_option_value[ 'price_prefix' ],
                    'weight'                  => $product_option_value[ 'weight' ],
                    'weight_prefix'           => $product_option_value[ 'weight_prefix' ]
                );
            }

            $product_option_data[] = array(
                'product_option_id'    => $product_option[ 'product_option_id' ],
                'product_option_value' => $product_option_value_data,
                'option_id'            => $product_option[ 'option_id' ],
                'name'                 => $product_option[ 'name' ],
                'type'                 => $product_option[ 'type' ],
                'value'                => $product_option[ 'value' ],
                'required'             => $product_option[ 'required' ]
            );
        }

        return $product_option_data;
    }

    public function getProductDiscounts( $product_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "product_discount WHERE product_id = '" . ( int )$product_id . "' AND customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC" );

        return $query->rows;
    }

    public function getProductImages( $product_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "product_image WHERE product_id = '" . ( int )$product_id . "' ORDER BY sort_order ASC" );

        return $query->rows;
    }

    public function getThcOfProduct($product_id) {
        $attribute_id = 34;

        $query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute 
        WHERE product_id = '" . (int)$product_id . "' 
        AND attribute_id = '" . (int)$attribute_id . "'");

        if ($query->num_rows) {
            return $query->row['text']; // Output the attribute value
        } else {
            return null;
        }
    }

    public function getProductRelated( $product_id ) {
        $THC_product_text = $this->getThcOfProduct($product_id);
        //echo $THC_product_text; die();

        $product_data = array();

        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_attribute 
        WHERE product_id <> '" . (int)$product_id . "' 
        AND text = '" . $THC_product_text . "'");

        foreach ( $query->rows as $result ) {
            $product_data[ $result[ 'product_id' ] ] = $this->getProduct( $result[ 'product_id' ] );
        }

        //print_r($product_data); die();

        return $product_data;
    }

    public function getProductLayoutId( $product_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "product_to_layout WHERE product_id = '" . ( int )$product_id . "' AND store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "'" );

        if ( $query->num_rows ) {
            return ( int )$query->row[ 'layout_id' ];
        } else {
            return 0;
        }
    }

    public function getCategories( $product_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "product_to_category WHERE product_id = '" . ( int )$product_id . "'" );

        return $query->rows;
    }

    public function getTotalProducts( $data = array() ) {
        $sql = 'SELECT COUNT(DISTINCT p.product_id) AS total';

        if ( !empty( $data[ 'filter_category_id' ] ) ) {
            if ( !empty( $data[ 'filter_sub_category' ] ) ) {
                $sql .= ' FROM ' . DB_PREFIX . 'category_path cp LEFT JOIN ' . DB_PREFIX . 'product_to_category p2c ON (cp.category_id = p2c.category_id)';
            } else {
                $sql .= ' FROM ' . DB_PREFIX . 'product_to_category p2c';
            }

            if ( !empty( $data[ 'filter_filter' ] ) ) {
                $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN ' . DB_PREFIX . 'product p ON (pf.product_id = p.product_id)';
            } else {
                $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product p ON (p2c.product_id = p.product_id)';
            }
        } else {
            $sql .= ' FROM ' . DB_PREFIX . 'product p';
        }

        $sql .= ' LEFT JOIN ' . DB_PREFIX . 'product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "'";

        if ( !empty( $data[ 'filter_category_id' ] ) ) {
            if ( !empty( $data[ 'filter_sub_category' ] ) ) {
                $sql .= " AND cp.path_id = '" . ( int )$data[ 'filter_category_id' ] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . ( int )$data[ 'filter_category_id' ] . "'";
            }

            if ( !empty( $data[ 'filter_filter' ] ) ) {
                $implode = array();

                $filters = explode( ',', $data[ 'filter_filter' ] );

                foreach ( $filters as $filter_id ) {
                    $implode[] = ( int )$filter_id;
                }

                $sql .= ' AND pf.filter_id IN (' . implode( ',', $implode ) . ')';
            }
        }

        if ( !empty( $data[ 'filter_name' ] ) || !empty( $data[ 'filter_tag' ] ) ) {
            $sql .= ' AND (';

            if ( !empty( $data[ 'filter_name' ] ) ) {
                $implode = array();

                $words = explode( ' ', trim( preg_replace( '/\s+/', ' ', $data[ 'filter_name' ] ) ) );

                foreach ( $words as $word ) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape( $word ) . "%'";
                }

                if ( $implode ) {
                    $sql .= ' ' . implode( ' AND ', $implode ) . '';
                }

                if ( !empty( $data[ 'filter_description' ] ) ) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape( $data[ 'filter_name' ] ) . "%'";
                }
            }

            if ( !empty( $data[ 'filter_name' ] ) && !empty( $data[ 'filter_tag' ] ) ) {
                $sql .= ' OR ';
            }

            if ( !empty( $data[ 'filter_tag' ] ) ) {
                $implode = array();

                $words = explode( ' ', trim( preg_replace( '/\s+/', ' ', $data[ 'filter_tag' ] ) ) );

                foreach ( $words as $word ) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape( $word ) . "%'";
                }

                if ( $implode ) {
                    $sql .= ' ' . implode( ' AND ', $implode ) . '';
                }
            }

            if ( !empty( $data[ 'filter_name' ] ) ) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape( utf8_strtolower( $data[ 'filter_name' ] ) ) . "'";
            }

            $sql .= ')';
        }

        if ( !empty( $data[ 'filter_manufacturer_id' ] ) ) {
            $sql .= " AND p.manufacturer_id = '" . ( int )$data[ 'filter_manufacturer_id' ] . "'";
        }

        $query = $this->db->query( $sql );

        return $query->row[ 'total' ];
    }

    public function getProfile( $product_id, $recurring_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . 'recurring r JOIN ' . DB_PREFIX . "product_recurring pr ON (pr.recurring_id = r.recurring_id AND pr.product_id = '" . ( int )$product_id . "') WHERE pr.recurring_id = '" . ( int )$recurring_id . "' AND status = '1' AND pr.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "'" );

        return $query->row;
    }

    public function getProfiles( $product_id ) {
        $query = $this->db->query( 'SELECT rd.* FROM ' . DB_PREFIX . 'product_recurring pr JOIN ' . DB_PREFIX . 'recurring_description rd ON (rd.language_id = ' . ( int )$this->config->get( 'config_language_id' ) . ' AND rd.recurring_id = pr.recurring_id) JOIN ' . DB_PREFIX . 'recurring r ON r.recurring_id = rd.recurring_id WHERE pr.product_id = ' . ( int )$product_id . " AND status = '1' AND pr.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' ORDER BY sort_order ASC" );

        return $query->rows;
    }

    public function getTotalProductSpecials() {
        $query = $this->db->query( 'SELECT COUNT(DISTINCT ps.product_id) AS total FROM ' . DB_PREFIX . 'product_special ps LEFT JOIN ' . DB_PREFIX . 'product p ON (ps.product_id = p.product_id) LEFT JOIN ' . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . ( int )$this->config->get( 'config_store_id' ) . "' AND ps.customer_group_id = '" . ( int )$this->config->get( 'config_customer_group_id' ) . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))" );

        if ( isset( $query->row[ 'total' ] ) ) {
            return $query->row[ 'total' ];
        } else {
            return 0;
        }
    }

    public function checkProductCategory( $product_id, $category_ids ) {

        $implode = array();

        foreach ( $category_ids as $category_id ) {
            $implode[] = ( int )$category_id;
        }

        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "product_to_category WHERE product_id = '" . ( int )$product_id . "' AND category_id IN(" . implode( ',', $implode ) . ')' );
        return $query->row;
    }
}
