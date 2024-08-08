<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Document class
*/
class Document {
	private $title;
	private $description;
	private $keywords;

	private $links = array();
	private $styles = array();
	private $scripts = array();

	/**
     * 
     *
     * @param	string	$title
     */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
     * 
	 * 
	 * @return	string
     */
	public function getTitle() {
		return $this->title;
	}

	/**
     * 
     *
     * @param	string	$description
     */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
     * 
     *
     * @param	string	$description
	 * 
	 * @return	string
     */
	public function getDescription() {
		return $this->description;
	}

	/**
     * 
     *
     * @param	string	$keywords
     */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
     *
	 * 
	 * @return	string
     */
	public function getKeywords() {
		return $this->keywords;
	}
	
	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
     */
	public function addLink($href, $rel) {
		$this->links[$href] = array(
			'href' => $href,
			'rel'  => $rel
		);
	}

	/**
     * 
	 * 
	 * @return	array
     */
	public function getLinks() {
		return $this->links;
	}

	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
	 * @param	string	$media
     */
	public function addStyle($href, $rel = 'stylesheet', $media = 'screen', $position = 'header') {
		$this->styles[$position][$href] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	/**
     * 
	 * 
	 * @return	array
     */
	public function getStyles($position = 'header') {
		if (isset($this->styles[$position])) {
			return $this->styles[$position];
		} else {
			return array();
		}
	}

	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$position
     */
	public function addScript($href, $position = 'header') {
		$this->scripts[$position][$href] = $href;
	}

	/**
     * 
     *
     * @param	string	$position
	 * 
	 * @return	array
     */
	public function getScripts($position = 'header') {
		if (isset($this->scripts[$position])) {
			return $this->scripts[$position];
		} else {
			return array();
		}
	}

	public function displayOrder(&$totals, $sum_tax_1, $sum_tax_2, $country_id = null, $totalNormalProduct = 0, $freeShipping = 0)
     {
          $this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

          $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_rate" . " order by rate desc");
  
          $taxObj = $query->rows;
          //print_r($taxObj); die();
          $tax_1 = (int) $taxObj[0]['rate'];
          $tax_2 = (int) $taxObj[0]['rate'];

          //echo $tax_2;

          $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting" . " where code = 'shipping_flat' and value <> 0 ORDER by value ASC");
          
          $costObj = $query->rows;

          foreach($costObj as $item)
          if($item['key'] == 'shipping_flat_cost')
            $costShiping = $item['value'];

          //echo $costShiping; die();

          $countItemIntoCart = 0;
          $valueCoupon = 0;

          //echo $countItemIntoCart . '//' . $valueCoupon;
          $titleShiping = 'Versandkostenpauschale';
            
          //echo count($totals); die();
          //print_r($totals);
            //print_r($country_id);
          if (count($totals) == 2) {
               if($sum_tax_2) {
                    $totals[2]['title'] = 'USt. ('.$tax_1.'%)';
                    $totals[2]['value'] = $sum_tax_2;

                    $totals[3] = $totals[1];
               }
               
               if(count($totals) != 4)
                 $totals[2] = $totals[1];

               $totals[1]['title'] = 'USt. ('.$tax_2.'%)';
               $totals[1]['value'] = $sum_tax_1;

//print_r($totals);
//echo $freeShipping;
               //echo count($totals);
               if($totalNormalProduct <= $freeShipping) {
                    //echo 'dd'; die();
                    if(count($totals) == 4) {
                         if($totalNormalProduct > $freeShipping || $totalNormalProduct == 0) {
                              $costShiping = 0;
                         } 

                         $totals[4] = $totals[3];
                         $totals[4]['value'] = $totals[4]['value'] + $costShiping;

                         $totals[3] = $totals[2];
                         $totals[2] = $totals[1];

                         $totals[1]['title'] = 'Versandkostenpauschale';
                         $totals[1]['value'] = $costShiping;

                         $totals[2]['value'] = $sum_tax_1 + round( $totals[1]['value'] - $totals[1]['value']/1.19 ,2);
                    }
                         else {
                              //die();
                              //print_r($totals);
                              if($totalNormalProduct > $freeShipping || $totalNormalProduct == 0) {
                                   $costShiping = 0;
                              } 

                              $totals[3] = $totals[2];
                              $totals[3]['value'] = $totals[3]['value'] + $costShiping;

                              $totals[2] = $totals[1];

                              $totals[1]['title'] = 'Versandkostenpauschale';
                              $totals[1]['value'] = $costShiping;

                              $totals[2]['value'] = $sum_tax_1 + round( $totals[1]['value'] - $totals[1]['value']/1.19 ,2);

                              // if only have unique coupon
                              if($valueCoupon >0 && $countItemIntoCart == 1) {
                                   //$totals[2]['value'] = 0;
                                   //$totals[2]['title'] = 'USt. (0%)';
                              } else if($valueCoupon > 0 && $countItemIntoCart >1) {
                                   //echo $valueCoupon/(1 + $tax_2/100);
                                   //echo round($valueCoupon - $valueCoupon/(1 + $tax_2), 2);
                                   //$totals[2]['value'] = $totals[2]['value'] - round($valueCoupon - $valueCoupon/(1 + $tax_2/100), 2);
                              }
//print_r($totals);
                         }
               } else {
                    //$totals[1]['value'] = $totals[1]['value'] - round($valueCoupon - $valueCoupon/(1 + $tax_2/100), 2);     
               }  
          } else if (count($totals) == 3) {
               //print_r($totals);

               // if is coupon
               if($totals['1']['code'] == 'coupon') {
                    if($sum_tax_2) {
                         $totals[3]['title'] = 'USt. ('.$tax_1.'%)';
                         $totals[3]['value'] = $sum_tax_2;
                      }
                      
                      //$totals[4] = $totals[2];
                      // if free shipping
                      if($totalNormalProduct > $freeShipping || $totalNormalProduct == 0) {
                         $totals[3] = $totals[2];
                         
                         $totals[2] = $totals[1];
                         $totals[2]['title'] = 'USt. ('.$tax_2.'%)';
                         $totals[2]['value'] = round($totals[3]['value'] - $totals[3]['value']/1.19, 2);

                         /*$totals[1] = $totals[1];
                         $totals[2]['value'] = ($totals[2]['value'] * -1 + $costShiping) * -1;
                         
                         $totals[1]['value'] = $costShiping;
                         $totals[1]['title'] = 'Versandkostenpauschale';

                         $temp = $totals[3];
                         $totals[3] = $totals[4];
                         $totals[4] = $temp;  */
                      } else {
                         $totals[4] = $totals[2];

                         $totals[3] = $totals[2];
                         $totals[3]['title'] = 'USt. ('.$tax_2.'%)';

                         $totals[2] = $totals[1];
                         
                         $value = ($totals[2]['value'] * -1 + $costShiping);

                         if($value <= $valueOrginalCoupon) 
                         $totals[2]['value'] = ($totals[2]['value'] * -1 + $costShiping) * -1;
                         else 
                         $totals[2]['value'] = $valueOrginalCoupon * -1;
                         
                         $totals[1]['value'] = $costShiping;
                         $totals[1]['title'] = 'Versandkostenpauschale';

                         //$totals[3]['value'] = $totals[0]['value'] + $totals[1]['value'] + $totals[2]['value'];
                         $temp = $totals[3];
                         $totals[3] = $totals[4];
                         $totals[4] = $temp;

                         $totals[3]['value'] = $totals[0]['value'] + $totals[1]['value'] + $totals[2]['value'];
                         $totals[4]['value'] = round($totals[3]['value'] - $totals[3]['value']/1.19, 2); 

                         //print_r($totals[4]); die();
                      }
               //print_r($totals); die();
                //print_r($_SESSION['coupon_id']);
               } else {
                     //print_r($totals); die(); 
				     $totals[4] = $totals[2];

                      $totals[2]['title'] = 'USt. ('.$tax_2.'%)';
                      $totals[2]['value'] = $sum_tax_1 + round( $totals[2]['value'] - $totals[2]['value']/(1 + $tax_2/100) ,2);

                      //$totals[2]['value'] = $totals[2]['value'] - round($valueCoupon - $valueCoupon/(1 + $tax_2/100), 2);
                      
                      if($valueCoupon >0 && $countItemIntoCart == 1) {
                         //$totals[2]['title'] = 'USt. (0%)';
                      } 
       
                      //print_r($totals);
                 
               }
          } else {
               //print_r($totals); die();
               // if is coupon
               if($totals[2]['code'] == 'coupon') {
                    //echo '11zz'; die();
                         $totals[4] = $totals[3];

                         //print_r($totals); die();

                         $value = ($totals[2]['value'] * -1 + $costShiping);

                         $value1 = $totals[0]['value'];
     
                         $value2 = $totals[1]['value'];
     
                         $value3 = $totals[2]['value'];
          
                         $value_ = ($value1 + $value2 + $value3) - ($value1 + $value2 + $value3) / (1 + $tax1/100);
                         //echo $value1 + $value2 + $value3;
                         $totals[3]['title'] = 'enthaltene MwSt. ('.$tax1.'%)';
                         //$totals[3]['value'] = $value_;
     
                         $totals[4]['value'] = ($value1 + $value2 + $value3);
                         // reduce value of total because shipping is 0
                         $totals[4]['value'] = $totals[4]['value'] - $totals[1]['value'];

                         $totals[3]['value'] = round($totals[4]['value'] - $totals[4]['value']/1.19, 2); 

                         // set shipping is 0
                         $totals[1]['value'] = 0;

                         //print_r($totals); die();

                    
                    
                    //print_r($totals); die();
               } else {
                    $totals[4] = $totals[3];

                    $value1 = $totals[0]['value'];
                    $value2 = $totals[1]['value'];
                    $value3 = $totals[2]['value'];
     
                    $value_ = ($value1 + $value2 + $value3) - ($value1 + $value2 + $value3) / (1 + $tax1/100);
                    //echo $value1 + $value2 + $value3;
                    $totals[3]['title'] = 'enthaltene MwSt. ('.$tax1.'%)';
                    $totals[3]['value'] = $value_;

                    //$totals[2]['value'] = $totals[2]['value'] - round($valueCoupon - $valueCoupon/(1 + $tax_2/100), 2);   
               }
          }
     }
}