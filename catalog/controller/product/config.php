<?php
// HTTP
define('HTTP_SERVER', 'https://www.svapo.de/');

// HTTPS
define('HTTPS_SERVER', 'https://www.svapo.de/');

// DIR
define('DIR_APPLICATION', '/kunden/homepages/31/d1006927561/htdocs/shop/catalog/');
define('DIR_SYSTEM', '/kunden/homepages/31/d1006927561/htdocs/shop/system/');
define('DIR_IMAGE', '/kunden/homepages/31/d1006927561/htdocs/shop/image/');
define('DIR_STORAGE', '/kunden/homepages/31/d1006927561/htdocs/storage/');
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
define('DB_HOSTNAME', 'db5015789122.hosting-data.io');
define('DB_USERNAME', 'dbu3533676');
define('DB_PASSWORD', 'FKEUcs?v!v2HtgtWvJ');
define('DB_DATABASE', 'dbs12878147');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// SMTP
// define('FROM_MAIL', 'info@svapo.de');
define('SMTP_HOST', 'smtp.ionos.de');
define('SMTP_HOST2', 'w01f9b2f.kasserver.com');
define('FROM_MAIL', 'info@svapo.de');
define('SMTP_USER', 'info@svapo.de');
define('SMTP_USER2', 'info@svapo.de');
define('SMTP_PASSWORD', 'E72wDgDqeAXdSijTYGx5');
define('SMTP_PASSWORD2', 'E72wDgDqeAXdSijTYGx5');
// define('SMTP_PORT', '465');
// define('SMTP_SSL', 'ssl');
// FTP FKEUcs?v!v2HtgtWvJ
// WHATSAPP
// https://wa.me/whatsappphonenumber?text=urlencodedtext

$whatsAppText = urldecode('Bitte sende mir das Passwort zu');
define('WHATSAPP_TEXT', 'Hallo svapo, ich habe folgende Frage:');
define('WHATSAPP_NUMBER', '+4915566200690');

// MAIL
define('ADDRESS', 'Schloss-Apotheke<br />
  Bürgeler Str. 35, 63075 Offenbach<br />		  
  Telefon: 069 60504047<br />
  E-Mail: info@svapo.de
');
define('ACCOUNT', 'Schloss-Apotheke<br />
  Deutsche Apotheker - und Ärztebank<br />
  IBAN: DE15 3006 0601 0047 5875 06<br />
  BIC: DAAEDEDDXXX
');
define('HEADER', '<table class="table" style="width: 680px;">
<thead>
    <tr>
      <td></td>	  
      <td align="right"><img src="'.HTTPS_SERVER.'images/Logo-SVAPO.svg" style="width:200px;"></td>
  </tr>
  <tr>
      <td>
'.ADDRESS.'
      </td>
      <td align="right"><p></p></td>
  </tr>
</thead>
</table>');

define('MAILHEADER', '<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8">
</head>
<body>
<style Type="text/css">
body { background-color: #ffffff; } p, h1, h2, h3, td, a { font-family:	Arial, Verdana; font-size:	12px; line-height: 	16px; color: #666666; margin: 0 0 16px 0; padding: 0; } a { color:#689C39; } p.small, p.small a, td.small, td.small a { font-size: 10px; line-height: 14px; color: #888888; } td { padding:6px 6px 6px 0;	margin:0; border:none !important; } h1, h2  { font-weight: 	bold; margin: 0 0 10px 0; } h1  { font-size: 16px; font-weight: normal; line-height: 20px; margin: 0 0 12px 0; } table { width: 100%; border:none !important; margin-bottom:20px;} table td table td table td table { width:auto !important; } table td table td table td table td { width:auto !important; padding:6px 6px 0 0; } colgroup { display: none; }
</style>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center">
	<table align="center" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px">
		<tr>
			<td align="center" style="background: #fff;" colspan="2">
				<img src="'.HTTPS_SERVER.'images/Logo-SVAPO.png" alt="svapo.de - Schloss Apotheke" style="width:120px;">
			</td>
		</tr>
		<tr><td>
');

define('FOOTER', '
	<table style="border-collapse: collapse; width: 100%;"  class="table top">
		<tr>
			<td colspan="2">
				<hr>							  
			</td>
		</tr>
		<tr>
		  <td valign="top" class="small">
'.ADDRESS.'
		  </td>
		  <td valign="top" class="small">
			 '.ACCOUNT.'<br>
			  USt-IdNr.: DE328219952
		  </td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center" class="small">
				<hr>							  
				<a href="'.HTTPS_SERVER.'agb/">AGB</a> | 
				<a href="'.HTTPS_SERVER.'impressum/">Impressum</a>
			</td>
		</tr>
    </table>');

define('PDF_ADDRESS', '
	'.ADDRESS.'
	<br />
	<br />
	'.ACCOUNT.'
	<br />
	USt-IdNr.: DE328219952
');
		
// MORPHEUS
define('MORPHEUS_HOMEPAGE', ['hometest/']);

// API KEY
// define('API_KEY', 'AIzaSyB8sG_3Vr2YQP6K_qt8isqRrWPjKE0gl8g');
define('API_KEY', 'AIzaSyBhhrT6ug-DPkEJtpHKuOkdKvygbkEtWxM');

// TOKEN API
define('TOKEN', 'AIzaSyAV4OcIzXCzfe8hQXYLXi_vnZ4jvLP7Ztc');

define('FREE_SHIPPING_KM', 20);

// ID OF ORDER SET INVOICE
define('ORDER_ID', 11);

// NEW STATUS ORDER AFTER CANCEL
define('NEW_ORDER_STATUS_AFTER_CANCEL', 27);

// DEFINE NEW STATUS1
define('STATUS1', 7);

// SUB FOLDER
define('SUB_FOLDER', '/');

// DEFINE CUSTOMMER GROUP ID AND ORDER STATUS ID BELONG TOGETHER
define('CUSTOMER_GROUP_ID', 2);
define('ORDER_STATUS_ID', 17);

// DEFINE ARRAY ORDER STATUS ID FOR SPECIAL EMAIL
define('ORDER_STATUS_ID_ARRAY', [3, 17]);

// DEFINE SPECIAL EMAIL
define('SPECIAL_EMAIL', 'bk@freiheit-gruppe.de');

// UPLOAD FILE
define('URL_UPLOAD', 'https://files.svapo.info/upload_file.php');
define('PATH_FILE_UPLOAD', 'https://files.svapo.info/uploads/');

// Überprüfe, ob das Cookie "needed" gesetzt ist
if (isset($_GET['setcookie'])) {
	define('NEW_COOKIE', 1);
	delCookieX('cookie_disclaimer');
} 
else if (isset($_COOKIE['cookie_disclaimer'])) {
	define('NEW_COOKIE', null);
} 
else {
	define('NEW_COOKIE', 1);
}

function delCookieX($key)
{
	$past = time() - 3600;
	setcookie($key, '', $past, '/');
}
