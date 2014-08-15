<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * role model
 * 
 * @author Hessian <hess.4x@gmail.com>
 */
class RoleModel extends BaseModel 
{
    public $TABLE_NAME = 'roles';

    public function getPermissions($role_ids)
    {
        if (!is_array($role_ids)) {
            $role_ids = array($role_ids);
        }

        // Administrator
        if (in_array('1', $role_ids)) {
            return array('allow' => array('ALL'), 'deny' => array('NONE'));
        }

        $allow = array('admin', 'manager/chgpwdform', 'manager/chgpwd');
        $deny  = array();

        foreach($role_ids as $role_id) {
            $role = $this->get($role_id);
            
            $allow = array_merge(explode('|', $role->allow), $allow);
            $deny = array_merge(explode('|', $role->deny), $deny);
        }

        return array('allow' => array_unique($allow), 'deny' => array_unique($deny));
    }
}

