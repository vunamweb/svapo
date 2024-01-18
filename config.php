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

   

