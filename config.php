<?php
// HTTP
define('HTTP_SERVER', 'http://localhost/pharmacy/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost/pharmacy/');

// DIR
define('DIR_APPLICATION', '/Applications/XAMPP/xamppfiles/htdocs/pharmacy/catalog/');
define('DIR_SYSTEM', '/Applications/XAMPP/xamppfiles/htdocs/pharmacy/system/');
define('DIR_IMAGE', '/Applications/XAMPP/xamppfiles/htdocs/pharmacy/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'pharmacy');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// SMTP
define('SMTP_HOST', 'w0118b8d.kasserver.com');
define('SMTP_USER', 'test@7sc.eu');
define('SMTP_PASSWORD', 'FKEUcsvv2HtgtWvJ');

// WHATSAPP
// https://wa.me/whatsappphonenumber?text=urlencodedtext
$whatsAppText = urldecode('Bitte sende mir das Passwort zu');
define('WHATSAPP_TEXT', 'FKEUcsvv2HtgtWvJ');
define('WHATSAPP_NUMBER', '+4917666855208');

// MAIL
define('HEADER', '<table class="table" style="width: 680px;">
<thead>
    <tr>
      <td></td>	  
      <td align="right"><img src="image/catalog/drugs.png" style="width:150px;"></td>
  </tr>
  <tr>
      <td>
          <span>Pharamcy Frankfurt</span>
              <br/>
              <br/>
      </td>
      <td align="right"><p>Pharamcy Frankfurt<br />
          Inh.: Andreas Schneider<br />
          Am Steinberg 24<br />
          60437 Frankfurt/Niedererlenbach<br />
          Tel.: 06101 – 41522<br />
          Fax: 06101 – 497484<br />
          info@Pharamcy</p></td>
  </tr>
</thead>
</table>');

define('MAILHEADER', '<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
</head>
<body>
<style Type="text/css">
body { background-color: #ffffff; } p, h1, h2, h3, td, a { font-family:	Arial, Verdana; font-size:	12px; line-height: 	16px; color: #666666; margin: 0 0 1px 0; padding: 0; } p.small, p.small a { font-size: 10px; line-height: 14px; color: #888888; } td { padding:6px 6px 6px 0;	margin:0; border:none !important; } h1, h2  { font-weight: 	bold; margin: 0 0 10px 0; } h1  { font-size: 16px; font-weight: normal; line-height: 20px; margin: 0 0 12px 0; } table { width: 100%; border:none !important; margin-bottom:20px;} table td table td table td table { width:auto !important; } table td table td table td table td { width:auto !important; padding:6px 6px 0 0; } colgroup { display: none; }
</style>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center">
	<table align="center" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px">
		<tr>
			<td align="center" style="background: #fff;" colspan="2">
			<img src="'.HTTPS_SERVER.'images/Logo-SVAPO.svg" style="width:200px;">
			</td>
		</tr>
		<tr><td>
');

define('FOOTER', '
<table style="border-collapse: collapse; width: 100%;"  class="table top">
			<tr>
				<td colspan="3">
					<hr>							  
				</td>
			</tr>
		<tr>
		  <td valign="top">Pharmacy<br>
			  Inh.: Andreas Schneider<br>
			  Am Steinberg 24<br>
			  60437 Frankfurt/Pharmacy
		  </td>
		  <td valign="top">
			  Deutsche Kreditbank<br>
			  Kto.: 1020 4280 64 / BLZ: 120 300 00<br>
			  IBAN: DE40 1203 0000 1020 4280 64<br>
			  BIC: BYLADEM1001<br>
			  USt-IdNr.: DE216963084
		  </td>
		  <td valign="top">
			  Tel.: 06101 – 41522<br>
			  Fax: 06101 – 497484<br>
			  info@Pharmacy
		  </td>
		</tr>
	</table>');
	
 // MORPHEUS
define('MORPHEUS_HOMEPAGE', ['hometest/']);

// API KEY
define('API_KEY', 'AIzaSyAV4OcIzXCzfe8hQXYLXi_vnZ4jvLP7Ztc');
define('FREE_SHIPPING_KM', 20);

// ID OF ORDER SET INVOICE
define('ORDER_ID', 12);

define('ADDRESS', 'Schloss-Apotheke, Paschalis Papadopoulos e. K.<br />
  Bürgeler Str. 35, 63075 Offenbach<br />		  
  Telefon: 0155 66 200 690<br />
  E-Mail: info@svapo.de
');
define('ACCOUNT', 'Schloss-Apotheke, Paschalis Papadopoulos e. K.<br />
  Deutsche Apotheker - und Ärztebank<br />
  IBAN: DE15 3006 0601 0047 5875 06<br />
  BIC: DAAEDEDDXXX
');

define('PDF_ADDRESS', '
	'.ADDRESS.'
	<br />
	<br />
	'.ACCOUNT.'
	<br />
	USt-IdNr.: DE328219952
');



   

