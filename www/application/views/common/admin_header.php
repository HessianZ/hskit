<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>管理后台</title>
        <base href='<?= base_url() ?>'/>
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/admin.css" rel="stylesheet" media="screen">
        <script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/data_table.js"></script>
        <script type="text/javascript" src="js/common.js"></script>

        <script type="text/javascript" src="js/jquery.tagsinput.min.js"></script>
        <link rel="stylesheet" href="css/jquery.tagsinput.css" type="text/css" />


        <script type="text/javascript" src="js/select2.min.js"></script>
        <script src="js/select2_locale_zh-CN.js"></script>
        <link rel="stylesheet" type="text/css" href="css/select2.css" />
        <link rel="stylesheet" type="text/css" href="css/select2-bootstrap.css" />

        <script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
        <link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.custom.min.css" type="text/css" />

        <style type="text/css">
        .ui-autocomplete {z-index: 65000}
        </style> 
    </head>
<?php
$RTR = & load_class('Router', 'core');
$controller = $RTR->fetch_class();
$method = $RTR->fetch_method();

$CI = & get_instance();
$CI->load->library('ACL');

$menus = array(
    array(
        "text" => "后台用户管理",
        "url" => "/admin/manager",
    ),
);

foreach ($menus as $key => $menu) {
    if (!isset($menu['submenu'])) {
        $url = explode('/', $menu['url']);
        $method = isset($url[3]) ? $url[3] : 'index';
        if (!$CI->acl->canAccess($url[2], $method)) {
            unset($menus[$key]);
        }
    } else {
        foreach ($menu['submenu'] as $sub => $submenu) {
            $url = explode('/', $submenu['url']);
            $method = isset($url[3]) ? $url[3] : 'index';
            if (!$CI->acl->canAccess($url[2], $method)) {
                unset($menus[$key]['submenu'][$sub]);
            }
        }
        if (empty($menus[$key]['submenu'])) {
            unset($menus[$key]);
        }
    }
}
?>
    <body>
        <div id="topnav">
            <div class="container navbar navbar-static-top">
                <a class="brand" href="#">BRAND NAME</a>
                <ul class="nav">
                    <li class="<?= $controller == "home" ? "active" : "" ?>"><a href="/admin/home">首页</a></li>
                <?php foreach ($menus as $menu) : ?>
                    
                    <?php if (!isset($menu['submenu'])) : ?>
                        <li class="<?= in_array($controller,explode('/', $menu['url'])) ? 'active' : ''; ?>" ><a href="<?= $menu['url']; ?>" ><?= $menu['text']; ?></a></li>
                    <?php else : ?>
                    <li class="dropdown">
                        <a id=<?= $menu['id']; ?> href="#" role="button" class="dropdown-toggle" data-toggle="dropdown"><?= $menu['text']; ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="<?= $menu['id']; ?>">
                        <?php foreach ($menu['submenu'] as $submenu) : ?>
                            <li role="presentation"><a href="<?= site_url($submenu['url']); ?>"><?= $submenu['text']; ?></a></li>
                        <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                <?php endforeach; ?>
                </ul>
                <ul class="nav pull-right">
                    <li id="user-menu" class="dropdown">
                        <a href="#" id="user-menu-drop" role="button" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <?= $this->session->userdata('login_name') ?> <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="user-menu-drop">
                            <li role="presentation"><a href="<?= site_url("/admin/manager/chgpwdform") ?>">修改密码</a></li>
                            <li role="presentation"><a href="<?= site_url("/auth/logout") ?>">退出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
