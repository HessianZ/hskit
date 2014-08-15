<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>管理后台</title>
        <base href='<?= base_url() ?>'/>
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/admin.css" rel="stylesheet" media="screen">
        <link rel="stylesheet" type="text/css" href="css/datepicker.css" />
        <script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datepicker.js"></script>
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
$controller = $this->router->fetch_class();
$method = $this->router->fetch_method();
$this->load->model('MenuModel');
$CI = &get_instance();

$menus = $CI->MenuModel->getHieraticalMenus();

foreach ($menus as $key => $menu) {
    if (!isset($menu->submenu)) {
        $url = explode('/', $menu->url);
        $act = isset($url[3]) ? $url[3] : 'index';
        if (!$CI->acl->canAccess($url[2], $act)) {
            unset($menus[$key]);
        }
    } else {
        foreach ($menu->submenu as $sub => $submenu) {
            $url = explode('/', $submenu->url);
            $act = isset($url[3]) ? $url[3] : 'index';
            if (!$CI->acl->canAccess($url[2], $act)) {
                unset($menu->submenu[$sub]);
            }
        }
        if (empty($menu->submenu)) {
            unset($menus[$key]);
        }
    }
}
?>
    <body>
        <div id="topnav">
            <div class="container navbar navbar-static-top">
                <a class="brand" href="/admin/home">DMP</a>
                <ul class="nav">
                <?php 
                foreach ($menus as $menu) : 
                    $act = '';
                    @list(,, $ctrl, $act) = explode('/', $menu->url);
                    $act = $act ?: 'index';
                ?>
                    
                    <?php if (!isset($menu->submenu)) : ?>
                    <li class="<?= ($ctrl == $controller && $act == $method) ? 'active' : ''; ?>" >
                        <a href="<?= $menu->url ?>" ><? if(!empty($menu->icon)) { ?><i class="icon-<?=$menu->icon?> icon-white"></i> <? } ?><?= $menu->text ?></a>
                    </li>
                    <?php else : ?>
                    <li class="dropdown">
                        <a id=<?= $menu->id ?> href="#" role="button" class="dropdown-toggle" data-toggle="dropdown"><? if(!empty($menu->icon)) { ?><i class="icon-<?=$menu->icon?> icon-white"></i> <? } ?><?= $menu->text ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="<?= $menu->id; ?>">
                        <?php foreach ($menu->submenu as $submenu) : ?>
                            <li role="presentation"><a href="<?= site_url($submenu->url); ?>"><? if(!empty($submenu->icon)) { ?><i class="icon-<?=$submenu->icon?>"></i> <? } ?><?= $submenu->text ?></a></li>
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
