<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function userid() {
    $ngx_uid = null;
    if (!empty($_SERVER['NGINX_USERID'])) {
        list($name, $ngx_uid) = explode('=', $_SERVER['NGINX_USERID']);
        $ngx_uid = nginx_userid_to_bytes($ngx_uid);
    } else if (!empty($_COOKIE['xuid'])) {
        $ngx_uid = base64_decode(str_replace(' ', '+', $_COOKIE['xuid']));
    }


    $pack = unpack('N*', $ngx_uid);

    if (!$ngx_uid || !is_array($pack) || count($pack) != 4) {
        setcookie("xuid", "", 0, '/');
        return null;
    }

    return ($pack[2] << 32) | $pack[4];
}

function nginx_userid_decode($str) {
    $str_unpacked = unpack('h*', base64_decode(str_replace(' ', '+', $str)));
    $str_split = str_split(current($str_unpacked), 8);
    $str_map = array_map('strrev', $str_split);
    $str_dedoded = strtoupper(implode('', $str_map));

    return $str_dedoded;
}

function nginx_userid_to_bytes($str) {
    $str_split = str_split($str, 8);
    $str_map = array_map('strrev', $str_split);
    $str_dedoded = strtolower(implode('', $str_map));
    return pack("h*", $str_dedoded);
}

?>
