<?php if ( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

/**
 * 框架基础Model，基于CI对数据库的一些基本操作进行了简单封装。
 * 
 * @author Hessian <hess.4x@gmail.com>
 */
class BaseModel extends CI_Model {

    protected $pk = array("id");
    protected $class_name = "";

    /**
     * 对象构造函数
     * 自动加载数据库对象和sql辅助函数库 
     */
    public function __construct() {
        try {
            parent::__construct();

            $this->load->database();
            $this->load->helper('sql');

            $this->class_name = substr(get_class($this), 0, -5);
        } catch (Exception $e) {
            $_error = & load_class('Exceptions', 'core');
            echo $_error->show_error("Model 初始化错误", $e->getMessage(), 'error_general', $e->getCode());
            exit;
        }
    }

    /**
     * 根据对象ID获取指定对象
     * @param int $id 对象ID
     * @return object 
     */
    function get() {
        $params = array_combine($this->pk, func_get_args());
        return $this->db->get_where($this->TABLE_NAME, $params, 1)->row();
    }

    /**
     * 根据唯一字段获取指定对象
     * @param string $key 字段名
     * @param string $value 字段值
     * @return object 
     */
    function getByUK($key, $value) {
        return $this->db->get_where($this->TABLE_NAME, array($key => $value), 1)->row();
    }

    /**
     * 
     * @param array $where
     * @return array
     */
    function getAll($arg1 = null, $arg2 = null) {

        if ($arg1 !== null && $arg2 !== null) {
            $this->db->where($arg1, $arg2);
        } else if (is_array($arg1)) {
            $this->db->where($arg1);
        }

        return $this->db->get($this->TABLE_NAME)->result();
    }

    /**
     * 根据查询条件返回匹配的结果集，支持分页。
     * @param mixed $search_params 查询条件数组或字串
     * @param array $orders 排序数组
     * @param int $start 结果集起始位置
     * @param int $count 获取数量
     * @return array 查询结果集
     */
    function query($search_params, $orders = null, $start = 0, $count = 20, $fields = null) {
        $where = build_where($this->db, $search_params);
        $order = build_order($orders);

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (!empty($order)) {
            $this->db->order_by($order);
        }

        if (!empty($fields)) {
            $this->db->select(implode(",", $fields));
        }

        $query = $this->db->get($this->TABLE_NAME, $count, $start);

        if ($query == false) {
            throw new Exception($this->db->_error_message(), $this->db->_error_number());
        }


        return $query->result();
    }

    /**
     * 返回查询条件匹配的记录总数
     * @param mixed $search_params 查询条件数组或字串
     * @return int 记录数
     */
    function queryCount($search_params) {
        $where = build_where($this->db, $search_params);

        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->from($this->TABLE_NAME)->count_all_results();
    }

    /**
     * 启用/禁用指定ID对象
     * @param int $id 对象ID
     * @return boolean 成功返回true，否则返回false
     */
    function enable($id) {
        $this->db->set('enabled', 'IF(enabled=1,0,1)', false);
        $args = func_get_args();
        $params = array_combine($this->pk, $args);
        $this->db->where($params);

        return $this->db->update($this->TABLE_NAME);
    }

    /**
     * 保存指定对象，若id为null则为创建。
     * 支持复合主键对象，除第一个参数外，所有参数按pk的顺序填入
     * @param array $data
     * @param int $id 要保存对象的ID
     * @return boolean 成功返回true(Insert返回ID)，否则返回false
     */
    function save($data, $id = null) {
        if ($id) {
            $args = func_get_args();
            array_shift($args);
            $params = array_combine($this->pk, $args);
            $ret = $this->db->update($this->TABLE_NAME, $data, $params);
        } else {
            $ret = $this->db->insert($this->TABLE_NAME, $data);

            if ($ret) {
                $ret = $this->db->insert_id();
            }

            // No AUTO_INCREMENT
            if ($ret === 0) {
                $ret = true;
            }
        }

        if ($ret === false) {
            throw new Exception($this->db->_error_message(), $this->db->_error_number());
        }

        return $ret;
    }

    /**
     * 删除指定对象
     * @param int $id 对象ID
     * @return boolean 成功返回true，否则返回false
     */
    function delete($id) {
        $args = func_get_args();
        $params = array_combine($this->pk, $args);

        return $this->db->delete($this->TABLE_NAME, $params);
    }

}

?>
