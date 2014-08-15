<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends HS_Controller {

    public $is_admin = true;

    public function index() {
        $this->load->view('common/admin_header');
        $this->load->view('admin');
        $this->load->view('common/admin_footer');
    }

}
