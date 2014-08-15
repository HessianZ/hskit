<?php

function redis_client($config_name = 'redis') {
    static $links = null;

    if (isset($links[$config_name])) {
        return $links[$config_name];
    }

    $CI = get_instance();

    $redis = new Redis();
    $redis_config = $CI->config->item($config_name);
    $success = false;

    if (empty($redis_config)) {
        throw new Exception("Redis config $config_name not found");
    }

    if (isset($redis_config['sock'])) {
        $success = $redis->connect($redis_config['sock']);
    }

    if (!$success && isset($redis_config['host'])) {
        $success = $redis->connect($redis_config['host'], $redis_config['port']);
    }

    if (!$success) {
        throw new Exception("Redis server not available");
    }

    if (!empty($redis_config['password']) && !$redis->auth($redis_config['password'])) {
        throw new Exception("Redis authorization failed");
    }

    if (isset($redis_config['db']) && is_numeric($redis_config['db'])) {
        if (!$redis->select((int) $redis_config['db'])) {
            throw new Exception("Redis select db[{$redis_config[db]} failed");
        }
    }

    $links[$config_name] = $redis;

    return $redis;
}
