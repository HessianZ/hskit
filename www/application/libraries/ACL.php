<?php
class ACL {

    public function canAccess($url_controller, $url_method) {
        $CI = & get_instance();
        
        $CI->load->library('session');

        $permissions = $CI->session->userdata('permissions');
        
        $pass = 0;
        $role_allow = $permissions['allow'];
        $role_deny = $permissions['deny'];
        
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
