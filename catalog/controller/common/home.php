<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->load->model('account/customer');

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');

		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_top_1'] = $data['content_top'][0];
		$data['content_top_2'] = $data['content_top'][1];
		
		//print_r($data['content_top']); die();

		/*$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['content_bottom_1'] = $data['content_bottom'][0];
		$data['content_bottom_2'] = $data['content_bottom'][1];
		$data['content_bottom_3'] = $data['content_bottom'][2];
		$data['content_bottom_4'] = $data['content_bottom'][3];
		$data['content_bottom_5'] = $data['content_bottom'][4];
		$data['content_bottom_6'] = $data['content_bottom'][5];
		$data['content_bottom_7'] = $data['content_bottom'][6];
		$data['content_bottom_8'] = $data['content_bottom'][7];
		$data['content_bottom_9'] = $data['content_bottom'][8];*/
		
        $data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		// GET MORPHEUS'S CONTENT
		$arrayContent = MORPHEUS_HOMEPAGE;
		$language = 'de/';

        foreach($arrayContent as $item) {
			$urlMorpheus = $item;
			$urlMorpheus = HTTP_SERVER . 'cms/' . $language . $urlMorpheus;
			//echo $urlMorpheus; die();

			$cookies = ($this->customer->isLogged()) ? "user_login=1" : "user_login=0";

			$context = stream_context_create([
				"http" => [
					"header" => "Cookie: $cookies\r\n"
				]
			]);
			
			$objectMorpheus = file_get_contents($urlMorpheus, true, $context);
			//echo $objectMorpheus; die();
			$objectMorpheus = str_replace(array('</body>', '</html>'), '', $objectMorpheus);
			$objectMorpheus = json_decode($objectMorpheus);

			//print_r($objectMorpheus); die();
			$contentMorpheus = $objectMorpheus->message;

			$data['content_morpheus'] = $contentMorpheus;
		}

		//$data['content_morpheus'] = $content;
		// END

		//$data['login'] = $this->customer->isLogged();

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
