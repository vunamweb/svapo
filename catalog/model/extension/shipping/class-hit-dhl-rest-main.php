<?php 
	/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

	if(!class_exists('DhlRest')){
	class DhlRest
	{
		public $mock_url = "https://api-mock.dhl.com/mydhlapi/";
		public $test_url = "https://express.api.dhl.com/mydhlapi/test/rates";
		public $live_url = "https://express.api.dhl.com/mydhlapi/rates";
		public $orderId;
		public $orderCurrency;
		public $dhlCurrency;
		public $dhlCurrConRate = 1;
		public $totPackWeg = 0;
		public $totPackCost = 0;
		public $totProdCost = 0;
		public $shipContent;
		public $disableDutiable = "N";
		public $serviceCode;
		public $trk_no;
		public $hitInvoiceB64;
		public function __construct()
		{
			
		}
		public function createRateReq($dhl_packs, $gen_set, $ship_addr, $rec_addr, $add_date=0)
		{
			$this->createPackTotals($dhl_packs);
			// Get the current GMT time
			$current_time = gmdate('Y-m-d\TH:i:s\G\M\TP');
			// Add 1 minute because DHL didn't accept current time
			$new_time = strtotime($current_time) + 60;
			// Format the new time in the same format
			$new_time_formatted = gmdate('Y-m-d\TH:i:s\G\M\TP', $new_time);
			if ($add_date > 0) {
				$new_time_formatted = gmdate('Y-m-d\TH:i:s\G\M\TP', strtotime("+".$add_date." day 14:00:00"));
			}
			// check rating date is week day or not
            $ratestimestamp = strtotime($new_time_formatted);
            $week_day = date('N', $ratestimestamp);
            if ($week_day > 5) {
                // If it's not a weekday, add 86400 x 2 seconds (48 hours) if saturday, 86400 seconds (24 hours) if sunday
                $new_rates_time = ($week_day == 7) ? ($ratestimestamp + 86400) : ($ratestimestamp + 86400 * 2);
                $new_time_formatted = gmdate('Y-m-d\TH:i:s\G\M\TP', $new_rates_time);
            }
			$rate_req = [];
			$rate_req['customerDetails']['shipperDetails'] = $this->makeAddrInfo($ship_addr);
			$rate_req['customerDetails']['receiverDetails'] = $this->makeAddrInfo($rec_addr);
			$rate_req['accounts'] = array(
				array(
					"typeCode" => "shipper",
					"number" => isset($gen_set['account_number']) ? $gen_set['account_number'] : ""
				)
			);
			$req_data['payerCountryCode'] = isset($ship_addr['country']) ? $ship_addr['country'] : "";
			if (isset($gen_set['pay_country']) && ($gen_set['pay_country'] == "R")) {
				$req_data['payerCountryCode'] = isset($rec_addr['country']) ? $rec_addr['country'] : "";
			} elseif (isset($gen_set['pay_country']) && ($gen_set['pay_country'] == "C") && isset($gen_set['pay_cust']) && !empty($gen_set['pay_cust'])) {
				$req_data['payerCountryCode'] = $gen_set['pay_cust'];
			}
			$rate_req['plannedShippingDateAndTime'] = $new_time_formatted;
			$rate_req['unitOfMeasurement'] = (isset($gen_set['weg_dim']) && $gen_set['weg_dim'] != true) ? 'metric' : 'imperial';
			$rate_req['isCustomsDeclarable'] = $this->checkDutiable($ship_addr, $rec_addr);
			if ($rate_req['isCustomsDeclarable'] == true) {
				$rate_req['monetaryAmount'][] = ['typeCode' => 'declaredValue', 'value' => round($this->totPackCost, 2), 'currency' => $this->dhlCurrency];
			}
			if (isset($gen_set['rate_insure']) && ($gen_set['rate_insure'] == true)) {
				$rate_req['monetaryAmount'][] = ['typeCode' => 'insuredValue', 'value' => round($this->totPackCost, 2), 'currency' => $this->dhlCurrency];
				$rate_req['valueAddedServices'][] = ['serviceCode' => 'II', 'value' => round($this->totPackCost, 2), 'currency' => $this->dhlCurrency];
			}
			if (isset($gen_set['enable_saturday_delivery']) && ($gen_set['enable_saturday_delivery'] == true)) {
				$rate_req['valueAddedServices'][] = ['serviceCode' => 'AA'];
			}
			$rate_req['packages'] = $this->makePackInfo($dhl_packs);
			return $rate_req;
			// echo "<pre>";print_r($rate_req);die();
		}
		private function makePackInfo($dhl_packs=[])
		{
			$pack_info = [];
			if (!empty($dhl_packs)) {
				foreach ($dhl_packs as $key => $pack) {
					$pack_info[] = [
						'weight' => isset($pack['Weight']['Value']) ? (float)$pack['Weight']['Value'] : 0.5,
						'dimensions' => [
							'length' => isset($pack['Dimensions']['Length']) ? (float)$pack['Dimensions']['Length'] : 1,
							'width' => isset($pack['Dimensions']['Width']) ? (float)$pack['Dimensions']['Width'] : 1,
							'height' => isset($pack['Dimensions']['Height']) ? (float)$pack['Dimensions']['Height'] : 1
						]
					];
				}
			}
			return $pack_info;
		}
		private function makeAddrInfo($addr=[])
		{
			$addr_info = [];
			$addr_info['postalCode'] = isset($addr['postcode']) ? $addr['postcode'] : "";
			$addr_info['cityName'] = isset($addr['city']) ? $addr['city'] : "";
			$addr_info['countryCode'] = isset($addr['country']) ? $addr['country'] : "";
			if (isset($addr['state']) && !empty($addr['state'])) {
				$addr_info['provinceCode'] = $addr['state'];
			}
			$addr_info['addressLine1'] = isset($addr['address1']) ? $addr['address1'] : "";
			if (isset($addr['address2']) && !empty($addr['address2'])) {
				$addr_info['addressLine2'] = $addr['address2'];
			}
			return $addr_info;
		}
		private function makeContactInfo($addr=[])
		{
			$cont_info = [];
			$cont_info['email'] = isset($addr['email']) ? $addr['email'] : "";
			$cont_info['phone'] = isset($addr['phone']) ? $addr['phone'] : "";
			$cont_info['companyName'] = (isset($addr['company']) && !empty($addr['company'])) ? $addr['company'] : (isset($addr['name']) ? $addr['name'] : "");
			$cont_info['fullName'] = isset($addr['name']) ? $addr['name'] : "";
			return $cont_info;
		}
		private function checkDutiable($ship_addr=[], $rec_addr=[])
		{
			$dutiable = true;
			if (isset($ship_addr['country']) && isset($rec_addr['country'])) {
				if ($ship_addr['country'] == $rec_addr['country']) {
					$dutiable = false;
				}
				if ($this->hit_dhl_is_eu_country($ship_addr['country'], $rec_addr['country'])) {
					$dutiable = false;
				}
			}
			return $dutiable;
		}
		private function hit_dhl_is_eu_country($countrycode, $destinationcode)
		{
			$eu_countrycodes = array(
				'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE',
				'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
				'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
				'HR', 'GR'
			);
			return (in_array($countrycode, $eu_countrycodes) && in_array($destinationcode, $eu_countrycodes));
		}
		private function createPackTotals($dhl_packages=[], $dhl_prods=[])
		{
			$total_value = 0;
			$total_weg = 0;
			$total_prod_value = 0;
			if ($dhl_packages) {
				foreach ($dhl_packages as $key => $parcel) {
					$total_value += $parcel['InsuredValue']['Amount'];
					$total_weg += $parcel['Weight']['Value'];
				}
			}
			// if (!empty($dhl_prods)) {
			// 	foreach ($dhl_prods as $key => $prod) {
			// 		$total_prod_value += isset($prod['unit_price_tax_incl']) ? $prod['unit_price_tax_incl'] : 0;
			// 	}
			// }
			$this->totPackWeg = $total_weg;
			$this->totPackCost = $total_value * $this->dhlCurrConRate;
			// $this->totProdCost = $total_prod_value * $this->dhlCurrConRate;
		}
		public function getRes($req_data=[], $mode="test", $api_key="", $api_sec="")
		{
			$req_url = "";
			if ($mode == "test") {
				$req_url =  $this->test_url;
			} elseif ($mode == "live") {
				$req_url =  $this->live_url;
			} 
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt_array($curl, [
				CURLOPT_URL => $req_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode($req_data),
				CURLOPT_HTTPHEADER => [
					"Authorization: Basic ".base64_encode($api_key.":".$api_sec),
					"Plugin-Name: HITTECHMARKET - DHL",
					"content-type: application/json"
				],
			]);
			$response = curl_exec($curl);
			if (!empty($response)) {
				$response = json_decode($response);
			}
			return $response;
		}
	
	}
}