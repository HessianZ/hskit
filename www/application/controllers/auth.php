<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends HS_Controller {

    public function index() {
        $redirect = $this->input->get_post('redirect');
        $this->load->view('auth/login', array('redirect' => $redirect));
    }

    public function login() {
        $this->load->helper('url');

        $login_name = $this->input->post('login_name');
        $password = $this->input->post('password');
        $redirect = $this->input->get_post('redirect') ?: site_url("/admin/home");

        $this->load->model('ManagerModel');

        try {
            $this->ManagerModel->login($login_name, $password);

            redirect($redirect);
        } catch (Exception $e) {
            $this->_error($e->getMessage(), 500, '登陆错误');
        }
    }

    public function logout() {
        $this->load->helper('url');

        $this->load->model('ManagerModel');

        try {
            $this->ManagerModel->logout();

            redirect(site_url("/auth"));
        } catch (Exception $e) {
            $this->_error($e->getMessage(), 500, '登陆错误');
        }
    }

}
