<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu extends HS_Controller {

    public $is_admin = true;

    public function index()
    {
        $this->load->view('common/admin_header');
        $this->load->view('menu/index');
        $this->load->view('common/admin_footer');
    }

    public function query()
    {
        $this->load->model('MenuModel');

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
                'count' => $this->MenuModel->queryCount($search_params),
                'rows' => $this->MenuModel->query($search_params, $orders, $start, $count)
            );
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }


    function addform()
    {
        $this->load->helper('form');
        $this->load->model('MenuModel');
        // 除首页外所有一级菜单
        $menus = $this->MenuModel->query(array('id:ne' => '1', 'pid' => 0));

        $menu = (object) array(
            'id' => null,
            'pid' => 0,
            'text' => '',
            'url' => '',
            'icon' => ''
        );
        
        $this->load->view('menu/form', array('menu' => $menu, 'menus' => $menus));
    }

    function editform($id = null)
    {
        $this->load->model('MenuModel');
        // 除首页外所有一级菜单
        $menus = $this->MenuModel->query(array('id:ne' => '1', 'pid' => 0));
        
        $id = isset($id) ? $id : $this->input->get('id');
        $menu = $this->MenuModel->get($id);

        $this->load->view('menu/form', array('menu' => $menu, 'menus' => $menus));
    }

    function save()
    {
        $this->load->model('MenuModel');

        $id = $this->input->post('id');
        $data = $this->input->post('data');

        $response = array('status' => 'ok');

        try {
            $this->MenuModel->save($data, $id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }

    function delete($id)
    {
        $this->load->model('MenuModel');

        $response = array('status' => 'ok');

        try {
            $this->MenuModel->delete($id);
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['data'] = $e->getMessage();
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }
}
