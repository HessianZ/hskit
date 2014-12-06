<?php

/**
 * controller
 *
 * @author Hessian <hess.4x@gmail.com>
 */

class HS_Controller extends CI_Controller
{
    public $is_admin = false;
    public $is_cache = true;
    public $cache_time = 60;
    public $template_dir = "";

    public function __construct()
    {
        parent::__construct();

        if ($this->is_admin)
        {
            $this->is_cache = false;
            $this->load->library('session');
            $id = $this->_getUid();
            
            $redirect = empty($_SERVER['REQUEST_URI']) ? '' : urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

            if (empty($id))
                $this->_error("请先<a href='" . site_url('/auth') . "?redirect=$redirect'>登陆</a>后再进行访问", 500,
                    '没有访问权限');
                        
            $url_controller = $this->router->fetch_class();
            $url_method = $this->router->fetch_method();
            
            if (!$this->acl->canAccess($url_controller, $url_method)) {
                $this->_error( "没有访问权限 <a href='javascript:history.go(-1);'>后退</a> 或者 以其它身份 <a href='".  site_url( '/auth' )."?redirect=$redirect'>登陆</a>", 500, '没有访问权限' );
            }
        }

        $global_cache = $this->config->item('global_cache');

        if ((int)trim($global_cache) == 0)
            $this->is_cache = false;

        $method = $this->input->server("REQUEST_METHOD");

        if ($method == 'GET' && $this->is_cache)
            $this->output->cache($this->cache_time);

        if (empty($this->template_dir))
            $this->template_dir = strtolower(get_class($this));
    }

    protected function _display($template, $params = array())
    {
        $this->load->view($this->is_admin ? 'common/admin_header' :
            'common/front_header');
        $this->load->view("$this->template_dir/$template", $params);
        $this->load->view($this->is_admin ? 'common/admin_footer' :
            'common/admin_footer');
    }

    protected function _error($message, $status_code = 500, $heading =
        'An Error Was Encountered')
    {
        $_error = &load_class('Exceptions', 'core');
        echo $_error->show_error($heading, $message, 'error_general', $status_code);
        exit;
    }

    protected function _getUid() {
        return $this->session->userdata('manager_id');
    }

    protected function _getLoginName() {
        return $this->session->userdata('login_name');
    }

}

?>
