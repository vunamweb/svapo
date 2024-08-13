<?php

$apiKey = 'EEKBudZ96102qzCKEkowt5ACl7y9dFtn'; // Setzen Sie hier Ihren API Key

function sendDhlShipmentRequest($url, $username, $password, $apiKey, $shipmentDetails) {
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
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentDetails));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if (curl_errno($ch)) {
		echo 'cURL Error: ' . curl_error($ch);
	} else {
		if ($httpCode == 200) {
			return json_decode($response, true);
		} else {
			echo "HTTP Request failed. Status code: $httpCode. Response: $response";
		}
	}
	
	curl_close($ch);
	return null;
}

// DHL API-Zugangsdaten
$username = 'sandy_sandbox';
$password = 'pass';


// Versanddetails
$shipmentDetails = [
	'plannedShippingDate' => date('Y-m-d'),
	'productCode' => 'V01PAK',
	'customerReference' => 'Your reference',
	'returnLabel' => false,
	'packages' => [
		[
			'weight' => 2.0,
			'dimensions' => [
				'length' => 10,
				'width' => 10,
				'height' => 10
			]
		]
	],
	'shipper' => [
		'name1' => 'Your Name',
		'address' => [
			'street' => 'Your Street',
			'houseNumber' => '1',
			'postalCode' => '12345',
			'city' => 'Your City',
			'countryCode' => 'DE'
		]
	],
	'receiver' => [
		'name1' => 'Recipient Name',
		'address' => [
			'street' => 'Recipient Street',
			'houseNumber' => '1',
			'postalCode' => '54321',
			'city' => 'Recipient City',
			'countryCode' => 'DE'
		]
	],
	'services' => [
		'visualCheckOfAge' => [
			'type' => 'A18'
		]
	],
	'label' => [
		'format' => 'PDF',
		'size' => 'A4'
	]
];

// URL der DHL Sandbox-API für das Erstellen von Sendungen
$url = 'https://api-sandbox.dhl.com/parcel/de/shipping/v2/shipments';

// Versand erstellen und Ergebnis ausgeben
$result = sendDhlShipmentRequest($url, $username, $password, $apiKey, $shipmentDetails);
if ($result) {
	echo "Tracking Code: " . $result['shipmentTrackingNumber'] . "\n";
	echo "Label URL: " . $result['label']['url'] . "\n";
} else {
	echo "Fehler beim Erstellen des DHL-Versands.\n";
}

?>
