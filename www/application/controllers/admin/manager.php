<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manager extends HS_Controller {

    public $is_admin = true;

    public function index() {
        $this->load->view('common/admin_header');
        $this->load->view('manager/index');
        $this->load->view('common/admin_footer');
    }

    public function query() {
        $this->load->model('ManagerModel');

        $orders = $this->input->post("orders");
        $count = $this->input->post("count");
        $start = $this->input->post("start");

        $search_params = $this->input->post('search');
        $search_params['login_name:ne'] = 'root';

        $response = array(
            'status' => 'ok',
            'data' => null
        );

        try {
            $response['data'] = array(
                'count' => $this->ManagerModel->queryCount($search_params),
                'rows' => $this->ManagerModel->query($search_params, $orders, $start, $count)
            );
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }


        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

    function enable() {
        $this->load->model('ManagerModel');

        $id = $count = $this->input->post("id");

        $response = array('status' => 'ok');

        try {
            $this->ManagerModel->enable($id);
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

        $this->load->model('RoleModel');
        $roles = $this->RoleModel->getAll();
        
        $manager = (object) array(
            'id' => '',
            'server_id' => 0,
            'login_name' => '',
            'roles' => '',
            'enabled' => '1',
        );

        $this->load->view('manager/form', array('roles' => $roles, 'manager' => $manager));
    }

    function editform($id = null) {
        $this->load->model('ManagerModel');
        $id = isset($id) ? $id : $this->input->get('id');

        $manager = $this->ManagerModel->get($id);

        $this->load->model('RoleModel');
        $roles = $this->RoleModel->getAll();

        $this->load->view('manager/form', array('roles' => $roles, 'manager' => $manager));
    }

    function save() {
        $this->load->model('ManagerModel');

        $id = $this->input->post('id');
        $roles = $this->input->post('roles');
        $data = $this->input->post('data');
        
        if (isset($roles)) {
            $data['roles'] = implode(",", $roles);
        }
    
        if (empty($id)) {
            $data['created'] = $data['modified'] = date('Y-m-d H:i:s');
        }

        $response = array('status' => 'ok');

        try {
            
            if (empty($id)) {
                if (empty($data['password'])) {
                    throw new Exception('请填写密码');
                }
            } else if (empty($data['password'])) {
                unset($data['password']);
            }
            
            $this->ManagerModel->save($data, $id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

    function delete($id) {
        $this->load->model('ManagerModel');

        $response = array('status' => 'ok');

        try {
            $this->ManagerModel->delete($id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

    function chgpwdform() {
        $this->load->view('common/admin_header');
        $this->load->view('manager/changepassword');
        $this->load->view('common/admin_footer');
    }

    function chgpwd() {
        $this->load->model('ManagerModel');

        $response = array('status' => 'ok');

        $manager_id = $this->session->userdata('manager_id');
        $oldpassword = $this->input->post('oldpassword');
        $password = $this->input->post('password');

        try {
            $manager = $this->ManagerModel->get($manager_id);

            if ($manager->password != $this->ManagerModel->toCipherText($oldpassword))
                throw new Exception('旧密码输入错误');

            $this->ManagerModel->changePassword($manager_id, $password);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

}
