<?php

defined('BASEPATH') or exit('No direct script access allowed!');

class Customers extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in())
            redirect('auth/login');
        $this->lang->load('customers', settings('language'));
        $this->load->model('customers_model', 'customers');

        $this->data['menu'] = array('menu' => 'sales', 'submenu' => 'customer');
    }

    public function index() {
        $this->template->_default();
        $this->template->table();
        $this->template->form();
        $this->load->js('assets/js/modules/sales/customers.js');

        $this->output->set_title(lang('customer_title'));
        $this->load->view('customers', $this->data);
    }

    public function get_list() {
        $this->input->is_ajax_request() or exit('No direct post submit allowed!');

        $page = $this->input->get('page');
        $filter = $this->input->get('fcol');
        $sort = $this->input->get('col');
        $size = $this->input->get('size');

        $output = array();
        $headers = array('', lang('customer_name_label'), lang('customer_email_label'), lang('customer_phone_label'), lang('customer_city_label'));
        $rows = array();

        $datas = $this->customers->get_all($page, $size, $filter, $sort);
        if ($datas) {
            foreach ($datas->result() as $data) {
                $row = array(
                    '' => '<td class="uk-text-center">'
                    . '<a href="#" class="btn-edit" data-id="' . encode($data->id) . '"><i class="md-icon material-icons">&#xE254;</i></a>'
                    . '<a href="' . site_url('sales/customers/delete/' . encode($data->id)) . '" class="ts_remove_row" data-name="' . $data->name . '"><i class="md-icon material-icons">&#xE872;</i></a>'
                    . '</td>',
                );
                $row[remove_space(lang('customer_name_label'))] = $data->name;
                $row[remove_space(lang('customer_email_label'))] = $data->email;
                $row[remove_space(lang('customer_phone_label'))] = $data->phone;
                $row[remove_space(lang('customer_city_label'))] = $data->city;
                array_push($rows, $row);
            }
        }
        $output['total_rows'] = $this->customers->count_all($filter);
        $output['headers'] = $headers;
        $output['rows'] = $rows;
        echo json_encode($output);
    }

    public function get($id) {
        $this->input->is_ajax_request() or exit('No direct post submit allowed!');

        $id = decode($id);
        $data = $this->main->get('customers', array('id' => $id));

        $output = json_encode($data);
        echo $output;
    }

    public function save() {
        $this->input->is_ajax_request() or exit('No direct post submit allowed!');

        $this->form_validation->set_rules('name', 'lang:customer_name_label', 'trim|required');
        $this->form_validation->set_rules('email', 'lang:customer_email_label', 'trim|required|valid_email');
        $this->form_validation->set_rules('address', 'lang:customer_address_label', 'trim|required');
        $this->form_validation->set_rules('phone', 'lang:customer_phone_label', 'trim|required');
        $this->form_validation->set_rules('city', 'lang:customer_city_label', 'trim|required');
        $this->form_validation->set_rules('postcode', 'lang:customer_postcode_label', 'trim|required|greater_than[5]');

        if ($this->form_validation->run() === true) {
            $data = $this->input->post(null, true);

            $method = $data['save_method'];
            unset($data['save_method']);

            if ($method == 'add') {
                $save = $this->main->insert('customers', $data);
            } else if ($method == 'edit') {
                $save = $this->main->update('customers', $data, array('id' => $data['id']));
            }

            if ($save !== false) {
                $return = array('message' => sprintf(lang('customer_save_success_message'), $data['name']), 'status' => 'success');
            } else {
                $return = array('message' => sprintf(lang('customer_save_failed_message'), $data['name']), 'status' => 'danger');
            }
        } else {
            $return = array('message' => validation_errors(), 'status' => 'danger');
        }
        echo json_encode($return);
    }

    public function delete($id) {
        $this->input->is_ajax_request() or exit('No direct post submit allowed!');

        $id = decode($id);
        $data = $this->main->get('customers', array('id' => $id));
        $delete = $this->main->delete('customers', array('id' => $id));
        if ($delete) {
            $return = array('message' => sprintf(lang('customer_save_success_message'), $data->name), 'status' => 'success');
        } else {
            $return = array('message' => sprintf(lang('customer_save_failed_message'), $data->name), 'status' => 'danger');
        }
        echo json_encode($return);
    }

}
