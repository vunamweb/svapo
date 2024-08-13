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
	curl_setopt($ch, CURLOPT_POSTFIELDS, $shipmentDetails);
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
$shipmentDetails = '
{
	"profile": "STANDARD_GRUPPENPROFIL",
	"shipments": [
	  {
		"product": "V01PAK",
		"billingNumber": "33333333330102",
		"refNo": "Order No. 1234",
		"shipper": {
		  "name1": "My Online Shop GmbH",
		  "addressStreet": "Sträßchensweg 10",
		  "additionalAddressInformation1": "2. Etage",
		  "postalCode": "53113",
		  "city": "Bonn",
		  "country": "DEU",
		  "email": "max@mustermann.de",
		  "phone": "+49 123456789"
		},
		"consignee": {
		  "name1": "Maria Musterfrau",
		  "addressStreet": "Kurt-Schumacher-Str. 20",
		  "postalCode": "53113",
		  "city": "Bonn",
		  "country": "DEU",
		  "email": "maria@musterfrau.de",
		  "phone": "+49 987654321"
		},
		"details": {
		  "dim": {
			"uom": "mm",
			"height": 100,
			"length": 200,
			"width": 150
		  },
		  "weight": {
			"uom": "g",
			"value": 500
		  }
		}
	  }
	]
  }
';

// URL der DHL Sandbox-API für das Erstellen von Sendungen
$url = 'https://api-sandbox.dhl.com/parcel/de/shipping/v2/orders';

// Versand erstellen und Ergebnis ausgeben
$result = sendDhlShipmentRequest($url, $username, $password, $apiKey, $shipmentDetails);

print_r($result); die();
if ($result) {
	echo "Tracking Code: " . $result['shipmentTrackingNumber'] . "\n";
	echo "Label URL: " . $result['label']['url'] . "\n";
} else {
	echo "Fehler beim Erstellen des DHL-Versands.\n";
}

?>
