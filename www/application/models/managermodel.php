<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * manager
 * 
 * @author Hessian <hess.4x@gmail.com>
 */
class ManagerModel extends BaseModel {

    public $TABLE_NAME = 'managers';
    private $salt = "{-A-FUCKCING-BITCH-}";

    public function hasLogon() {
        $this->load->library('session');

        $manager_id = $this->session->userdata('manager_id');

        if (empty($manager_id)) {
            return false;
        }

        return true;
    }

    public function login($login_name, $password) {
        $this->load->library('session');

        $rs = $this->db->get_where($this->TABLE_NAME, array('login_name' => $login_name), 1);

        if ($rs->num_rows === 0) {
            throw new Exception("用户名错误！");
        }

        $user = $rs->row();

        if ($user->password != $this->toCipherText($password)) {
            throw new Exception("密码错误！");
        }

        $session_data = array(
            'manager_id' => $user->id, 
            'login_name' => $user->login_name
        );

        if (!empty($user->roles)) {
            $this->load->model('RoleModel');
            $roles = explode(',', $user->roles);
            $session_data['permissions'] = $this->RoleModel->getPermissions($roles);
        }

        $this->session->set_userdata($session_data);

        return $user;
    }

    public function logout() {
        $this->load->library('session');

        $this->session->sess_destroy();
    }

    function save($data, $id = null) {
        if (isset($data['password'])) {
            $data['password'] = $this->toCipherText($data['password']);
        }

        return parent::save($data, $id);
    }

    function changePassword($user_id, $password) {
        $this->db->where("id", $user_id);
        return $this->db->update($this->TABLE_NAME, array('password' => $this->toCipherText($password)));
    }

    function toCipherText($plaintext) {
        return md5($this->salt . $plaintext . $this->salt);
    }

}
