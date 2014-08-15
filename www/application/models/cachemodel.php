<?php if ( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

/**
 * 封装Cache操作。
 * 
 * @author Hessian <hess.4x@gmail.com>
 */
class CacheModel extends BaseModel {
    public $cache_key_prefix = "";
    protected $cache;
    protected $cache_expire = 3600;

    function __construct()
    {
        parent::__construct();

        $this->load->helper('redis');

        $this->cache = &redis_client();

        $this->cache_key_prefix = strtolower($this->class_name) . ":";
    }

    /**
    * $mixed keys 
    */
    function getCacheKey($args) {
        if (!is_array($args)) {
            $args = array($args);
        }

        foreach ( $args as $arg ) {
            if (empty($arg)) {
                return null;
            }
        }

        $key = $this->cache_key_prefix . implode("-", $args);

        return $key;
    }

    function get($id) {
        $args = func_get_args();
        $key = $this->getCacheKey($args);

        if ($ret = $this->cache->get($key)) {
            $ret = unserialize($this->cache->get($key));
        } else {
            $ret = call_user_func_array( 'parent::' . explode("::", __METHOD__)[1], $args );
            if ($ret) {
                $this->cache->setex($key, $this->cache_expire, serialize($ret));
            }
        }

        return $ret;
    }

    function mget($ids) {
        $ids = is_array($ids) ? $ids : func_get_args();

        $keys = array();
        foreach ($ids as $id) {
            $keys[] = $this->getCacheKey($id);
        }

        $caches = $this->cache->mGet($keys);

        $ret = array();
        if ($caches) {
            foreach ($caches as $index => $cache) {
                if ($cache) {
                    $ret[] = unserialize($cache);
                } else {
                    $ret[] = $this->get($ids[$index]);
                }
            }
        }

        return $ret;
    }

    function getByUK($name, $value) {
        $key = $this->cache_key_prefix . "UK:$name-$value";

        $ret = $pks = null;

        if ($this->cache->exists($key)) {
            $pks = unserialize($this->cache->get($key));
        } else {
            $ret =  $this->db->select($this->pk)->get_where($this->TABLE_NAME, array($name => $value), 1)->row_array();
            if ($ret) {
                $pks = array_values($ret);
                $this->cache->setex($key, $this->cache_expire, serialize($pks));
            }
        }

        if ($pks) {
            $ret = call_user_func_array( array($this, 'get'), $pks );
        }

        return $ret;
    }

    function save($data, $id = null) {
        $args = func_get_args();

        $ret = call_user_func_array( 'parent::' . explode("::", __METHOD__)[1], $args );

        if ($id) {
            $pks = array_slice($args, 1);
            $key = $this->getCacheKey($pks);
            $this->cache->del($key);
        }

        return $ret;
    }

    function delete($id) {
        $args = func_get_args();

        $ret = call_user_func_array( 'parent::' . explode("::", __METHOD__)[1], $args );

        if ($id) { 
            $key = $this->getCacheKey($args);
            $this->cache->del($key);
        }

        return $ret;
    }

    function enable($id) {
        $args = func_get_args();

        $ret = call_user_func_array( 'parent::' . explode("::", __METHOD__)[1], $args );

        if ($id) { 
            $key = $this->getCacheKey($args);
            $this->cache->del($key);
        }

        return $ret;
    }
    
}

