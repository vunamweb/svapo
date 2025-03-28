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
define('SMTP_HOST', 'smtp.ionos.de');
define('SMTP_USER', 'info@svapo.de');
define('SMTP_PASSWORD', 'Bertolli11');

define('SMTP_HOST2', 'w01f9b2f.kasserver.com');
define('FROM_MAIL', 'info@svapo.de');
define('SMTP_USER2', 'info@svapo.de');
define('SMTP_PASSWORD2', 'JzYdkmnniaN3mdBvPYr8');

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

define('ADDRESS', 'Schloss-Apotheke<br />
  Bürgeler Str. 35, 63075 Offenbach<br />		  
  Telefon: 0155 66 200 690<br />
  E-Mail: info@svapo.de
');
define('ACCOUNT', 'Schloss-Apotheke<br />
  Deutsche Apotheker - und Ärztebank<br />
  IBAN: DE15 3006 0601 0047 5875 06<br />
  BIC: DAAEDEDDXXX
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
	
 // MORPHEUS
define('MORPHEUS_HOMEPAGE', ['hometest/']);

// API KEY
define('API_KEY', 'AIzaSyAV4OcIzXCzfe8hQXYLXi_vnZ4jvLP7Ztc');
define('FREE_SHIPPING_KM', 20);

// TOKEN API
define('TOKEN', 'AIzaSyAV4OcIzXCzfe8hQXYLXi_vnZ4jvLP7Ztc');

// ID OF ORDER SET INVOICE
define('ORDER_ID', 12);

// SUB FOLDER
define('SUB_FOLDER', '/pharmacy/');

define('PDF_ADDRESS', '
	'.ADDRESS.'
	<br />
	<br />
	'.ACCOUNT.'
	<br />
	USt-IdNr.: DE328219952
');

// DEFINE CUSTOMMER GROUP ID AND ORDER STATUS ID BELONG TOGETHER
define('CUSTOMER_GROUP_ID', 2);
define('ORDER_STATUS_ID', 17);

// DEFINE ARRAY ORDER STATUS ID FOR SPECIAL EMAIL
define('ORDER_STATUS_ID_ARRAY', [3, 17, 7]);

// NEW STATUS ORDER AFTER CANCEL
define('NEW_ORDER_STATUS_AFTER_CANCEL', 27);

// DEFINE SPECIAL EMAIL
define('SPECIAL_EMAIL', 'vu@pixeldusche.com');

// DEFINE NEW STATUS1
define('STATUS1', 7);

//define('PATH_FILE_UPLOAD', 'https://pharmacy.svapo.de/uploads/');


// UPLOAD FILE
//define('URL_UPLOAD', 'https://www.morpheus-cms.de/vu/upload_file.php');
//define('PATH_FILE_UPLOAD', 'https://www.morpheus-cms.de/vu/uploads/');
//define('URL_UPLOAD', 'https://pharmacy.svapo.de/upload_file.php');
//define('PATH_FILE_UPLOAD', 'https://pharmacy.svapo.de/uploads/');
define('URL_UPLOAD', 'https://files.svapo.info/upload_file.php');
define('PATH_FILE_UPLOAD', 'https://files.svapo.info/uploads/');

// STATUS CANCEL
define('STATUS_CANCEL', 9);

// CRONTAB1
define('DAY_CRONTAB1', 5);
define('SUBJECT_CRONTAB1', 'Auftragsbestätigung Erinnerung');

// CRONTAB2
define('DAY_CRONTAB2', 10);
define('SUBJECT_CRONTAB2', 'Crontab2');