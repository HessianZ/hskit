<?php
class ACL {

    public static $permissions = array(
        "Administrator" => array(
            "allow" => "ALL",
            "deny" => "NONE"
        ),
        "Manager" => array(
            "allow" => "ALL",
            "deny" => "manager/*"
        )
    );

    public function canAccess($url_controller, $url_method) {
        $CI = & get_instance();
        
        $CI->load->library('session');
        $CI->load->model("ManagerModel");
        $id = $CI->session->userdata('manager_id');
        
        $manager = $CI->ManagerModel->get($id);
        $roles = explode(',', $manager->roles);
        
        $role_allow = array('admin', 'manager/chgpwdform', 'manager/chgpwd');
        $role_deny = array();
        
        foreach ($roles as $role) {
            if ($role == 'Administrator') {
                return true;
            }
            
            $role_allow = array_merge(explode('|', self::$permissions[$role]['allow']), $role_allow);
            $role_deny = array_merge(explode('|', self::$permissions[$role]['deny']), $role_deny);
            
        }
        
        $pass = 0;
        $role_allow = array_unique($role_allow);
        $role_deny = array_unique($role_deny);
        
        foreach ($role_allow as $allow) {
            if ($allow == 'ALL') {
                $pass += 1;
            } else {
                $arr = explode('/', $allow);
                if ($arr[0] == $url_controller) {
                    $method = isset($arr[1]) ? $arr[1] : '*';
                    if ($method == $url_method || $method == '*') {
                        $pass += 2;
                    }
                }
            }
        }
        
        foreach ($role_deny as $deny) {
            if ($deny == 'ALL') {
                $pass -= 1;
            } elseif ($deny != 'NONE') {
                $arr = explode('/', $deny);
                if ($arr[0] == $url_controller) {
                    $method = isset($arr[1]) ? $arr[1] : '*';
                    if ($method == $url_method || $method == '*') {
                        $pass -= 2;
                    }
                }
            }
        }
        
        return $pass > 0;
    }
}
