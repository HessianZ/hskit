<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * menu model
 * 
 * @author Hessian <hess.4x@gmail.com>
 */
class MenuModel extends BaseModel 
{
    public $TABLE_NAME = 'menus';

    public function getHieraticalMenus($parent_id = 0)
    {
        $menus = $this->getChildren($parent_id);

        if (empty($menus)) {
            return array();
        }
        
        foreach ($menus as &$menu) {
            $children = $this->getChildren($menu->id);
            if ($children) {
                $menu->submenu = $children;
            }
        }

        return $menus;
    }

    public function getChildren($menu_id)
    {
        return $this->db->order_by('order_no asc, id asc')->get_where($this->TABLE_NAME, array('pid'=>$menu_id))->result();
    }
}

