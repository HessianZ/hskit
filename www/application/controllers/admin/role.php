<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Role extends HS_Controller {

    public $is_admin = true;

    public function index() {
        $this->load->view('common/admin_header');
        $this->load->view('role/index');
        $this->load->view('common/admin_footer');
    }

    public function query() {
        $this->load->model('RoleModel');

        $fields = $this->input->post("fields");
        $orders = $this->input->post("orders");
        $count = $this->input->post("count");
        $start = $this->input->post("start");

        $search_params = $this->input->post('search');

        $response = array(
            'status' => 'ok',
            'data' => null
        );

        try {
            $response['data'] = array(
                'count' => $this->RoleModel->queryCount($search_params),
                'rows' => $this->RoleModel->query($search_params, $orders, $start, $count)
            );
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }


        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }


    function addform() {
        $this->load->helper('form');

        $this->load->view('role/form');
    }

    function editform($id = null) {
        $id = isset($id) ? $id : $this->input->get('id');

        $this->load->model('RoleModel');

        $role = $this->RoleModel->get($id);

        $this->load->view('role/form', array('role' => $role));
    }

    function save() {
        $this->load->model('RoleModel');

        $id = $this->input->post('id');
        $data = $this->input->post('data');
        
        if (empty($id)) {
            $data['created'] = $data['modified'] = time();
        }

        $response = array('status' => 'ok');

        try {
            $this->RoleModel->save($data, $id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

    function delete($id) {
        $this->load->model('RoleModel');

        $response = array('status' => 'ok');

        try {
            $this->RoleModel->delete($id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }



}
