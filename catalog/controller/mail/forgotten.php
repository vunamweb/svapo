<?php
class ControllerMailForgotten extends Controller {
	public function index(&$route, &$args, &$output) {			            
		$this->load->language('mail/forgotten');

		$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_change'] = $this->language->get('text_change');
		$data['text_ip'] = $this->language->get('text_ip');
		
		$data['reset'] = str_replace('&amp;', '&', $this->url->link('account/reset', 'code=' . $args[1], true));
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		/*$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($args[0]);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
		$mail->setText($this->load->view('mail/forgotten', $data));
		$mail->send();*/

		$subject = html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');

		$this->document->sendMailSMTP($args[0], $subject, SMTP_USER, $this->config->get('config_email'), $this->load->view('mail/forgotten', $data), $this->session->data['upload_file']);
			
	}

	function sendMailSMTP($to, $subject, $from, $fromName, $message, $image = null)
    {
		$mail = new PHPMailer();
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPDebug = 0; // enables SMTP debug information (for testing)
		$mail->SMTPAuth = true; // enable SMTP authentication
		$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
		$mail->Host = SMTP_HOST; // sets GMAIL as the SMTP server
		$mail->Port = 465; // set the SMTP port for the GMAIL server
		$mail->Username = SMTP_USER; // GMAIL username
		$mail->Password = SMTP_PASSWORD;
		$mail->CharSet = 'UTF-8';
		$mail->AddAddress($to);
		//$mail->addBcc("vukynamkhtn@gmail.com");
		$mail->Subject = $subject;
		$mail->FromName = $fromName;
		$mail->From = $from;
		$mail->IsHTML(true);
		$mail->Body = $message;

		if (!$mail->Send()) {
			//echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			//echo "Message sent!";
		}
    }
}
