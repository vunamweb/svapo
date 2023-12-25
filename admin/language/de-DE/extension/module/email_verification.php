<?php
// Heading
$_['heading_title']    = 'E-Mail-Verifizierung';

// Text
$_['text_edit']        = 'Modul bearbeiten';
$_['text_extension']   = 'Erweiterungen';
$_['text_success']     = 'Erfolg: Sie haben das Modul geändert!';
$_['text_approve_subject']  = 'Bestätige deine Email-Adresse';
$_['text_approve_content1'] = 'Um Ihr Konto zu aktivieren, klicken Sie auf den untenstehenden Link oder kopieren Sie ihn und fügen Sie ihn in die Adressleiste des Browsers ein:&lt;/div&gt;&lt;table width=&quot;100%&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot; style=&quot;width:100%&quot;&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;   &lt;div class=&quot;table-responsive&quot;&gt;   &lt;table class=&quot;link&quot; cellspacing=&quot;0&quot; cellpadding=&quot;0&quot; width=&quot;auto&quot; style=&quot;width:auto;&quot;&gt;        &lt;tbody&gt;            &lt;tr&gt;                &lt;td&gt;&lt;a href=&quot;{{ verification_link|replace({\'&amp;amp;\':\'&amp;amp;\'}) }}&quot; target=&quot;_blank&quot;&gt;&lt;b&gt;{{ verification_link }}&lt;/b&gt; &lt;/a&gt;                &lt;/td&gt;            &lt;/tr&gt;        &lt;/tbody&gt;    &lt;/table&gt; &lt;/div&gt;  &lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;&lt;div&gt;&lt;br&gt;Nach der Anmeldung können Sie auf andere Dienste zugreifen, darunter die Überprüfung früherer Bestellungen, das Drucken von Rechnungen und die Bearbeitung Ihrer Kontoinformationen.    &lt;br&gt;&lt;br&gt;&lt;/div&gt;&lt;div class=&quot;last&quot;&gt;Thanks,    &lt;br style=&quot;line-height:18px;&quot;&gt;&lt;a href=&quot;{{ store_url|replace({\'&amp;amp;\':\'&amp;amp;\'}) }}&quot; target=&quot;_blank&quot;&gt;&lt;b&gt;{{ store_name }}&lt;/b&gt;&lt;/a&gt;&lt;/div&gt;';
$_['text_approve_heading']  = 'Willkommen zu {{ store_name }}';

// Entry
$_['entry_status']     = 'Status';

// Error
$_['error_permission'] = 'Warnung: Sie haben keine Berechtigung, das Modul zu ändern!';