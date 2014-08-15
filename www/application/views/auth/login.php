<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
        <title>Admin - H.A.S.</title>
        <style>
            #loginform { width: 245px; margin: 150px auto 0 } 
        </style>
    </head>
    <body>
        <div class="container">
            <form id="loginform" class="well " name="loginForm" action="<?= site_url('/auth/login') ?>" method="post" autocomplete="off" onsubmit="return checkForm(this);">
                <fieldset>
                    <legend>欢迎登录</legend>
                    <div>
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-user"></i></span>
                            <input type="text" id="loginname"  name="login_name" value="" autofocus="autofocus"/>
                        </div>
                    </div>
                    <div>
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-lock"></i></span>
                            <input type="password" id="password"  name="password" value=""/>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">登　录</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <script type="text/javascript" src="/js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript">
function checkForm(form)
{
    if ($("#loginname").val() == null || $("#loginname").val() == "") {
        alert("您未填写用户名！");
        return false;
    }
    if ($("#password").val() == null || $("#password").val() == "") {
        alert("您未填写密码！");
        return false;
    }
    return true;
}
        </script>
    </body>
</html>
