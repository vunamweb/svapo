<?php
class ControllerExtensionReportCustomerOrder extends Controller {
	public function index() {
		$this->load->language('extension/report/customer_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_customer_order', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/report/customer_order', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/customer_order', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_customer_order_status'])) {
			$data['report_customer_order_status'] = $this->request->post['report_customer_order_status'];
		} else {
			$data['report_customer_order_status'] = $this->config->get('report_customer_order_status');
		}

		if (isset($this->request->post['report_customer_order_sort_order'])) {
			$data['report_customer_order_sort_order'] = $this->request->post['report_customer_order_sort_order'];
		} else {
			$data['report_customer_order_sort_order'] = $this->config->get('report_customer_order_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/customer_order_form', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/customer_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
			
	public function exPortCSV($data) {
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=data.csv' );

		ob_start();

		$delimiter = ';';

		$output = fopen( 'php://output', 'w' );

		$headers = array();

		$headers[0] = 'ID';
		$headers[1] = 'Kunde';
		$headers[2] = 'Email';
		$headers[3] = 'Kundengruppe';
		$headers[4] = 'Status';
		$headers[5] = 'Bestellnummer';
		$headers[6] = 'Gram';
		$headers[7] = 'Gesamt';
		
		fputcsv( $output, $headers, $delimiter );
		
		foreach ( $data as $item )
          fputcsv( $output, $item, $delimiter );

		fclose( $output );
		
		die();
    }

	public function report() {
		$this->load->language('extension/report/customer_order');

		if (isset($this->request->get['export'])) {
			$export = $this->request->get['export'];
		} else {
			$export = '';
		}

		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = '';
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = 0;
		}

		if (isset($this->request->get['filter_gram_from'])) {
			$filter_gram_from = $this->request->get['filter_gram_from'];
		} else {
			$filter_gram_from = '';
		}

		if (isset($this->request->get['filter_gram_to'])) {
			$filter_gram_to = $this->request->get['filter_gram_to'];
		} else {
			$filter_gram_to = '';
		}

		if (isset($this->request->get['filter_price_from'])) {
			$filter_price_from = $this->request->get['filter_price_from'];
		} else {
			$filter_price_from = '';
		}

		if (isset($this->request->get['filter_price_to'])) {
			$filter_price_to = $this->request->get['filter_price_to'];
		} else {
			$filter_price_to = '';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		

		$this->load->model('extension/report/customer');

		$data['customers'] = array();

		$filter_data = array(
			'filter_date_start'			=> $filter_date_start,
			'filter_date_end'			=> $filter_date_end,
			'filter_customer'			=> $filter_customer,
			'filter_gram_from'			=> $filter_gram_from,
			'filter_gram_to'			=> $filter_gram_to,
			'filter_price_from'			=> $filter_price_from,
			'filter_price_to'			=> $filter_price_to,
			'filter_order_status_id'	=> $filter_order_status_id,
			'start'						=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'						=> $this->config->get('config_limit_admin')
		);

		$customer_total = count($this->model_extension_report_customer->getOrders_adjust($filter_data)); //$this->model_extension_report_customer->getTotalOrders($filter_data);
        //echo $customer_total;
		$results = $this->model_extension_report_customer->getOrders_adjust($filter_data);

		if($export)
		  $this->exPortCSV($results);

		foreach ($results as $result) {
			$data['customers'][] = array(
				'customer'       => $result['customer'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'orders'         => $result['orders'],
				'products'       => $result['products'],
				'total'          => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'edit'           => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'], true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode($this->request->get['filter_customer']);
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_order' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status_id'] = $filter_order_status_id;
		$data['filter_gram_from'] = $filter_gram_from;
		$data['filter_gram_to'] = $filter_gram_to;
		$data['filter_price_from'] = $filter_price_from;
		$data['filter_price_to'] = $filter_price_to;
		

		return $this->load->view('extension/report/customer_order_info', $data);
	}
}