<script type="text/javascript">
    function changePassword(form)
    {
        if (form.oldpassword.value == '')
            return alert('请输入旧密码')

        if (form.password.value == '')
            return alert('请输入新密码')

        if (form.password.value != form.repwd.value)
            return alert('重复密码输入错误')

        $.post(form.action, $(form).serialize(), function(response) {
            if (response.status == 'ok') {
                alert('密码修改成功')
                form.reset()
            } else {
                alert(response.data)
            }
        }, 'json')
    }
</script>

<div class="container">
    <form class="well span4 offset4" style="margin-top: 50px" action="<?=base_url('/admin/manager/chgpwd')?>" method="POST" onsubmit="changePassword(this);return false;">
        <fieldset>
            <legend>修改密码</legend>
            <label>旧密码</label>
            <input type="password" name="oldpassword" value="" class="span4" required />
            <label>新密码</label>
            <input type="password" name="password" value="" class="span4" required />
            <label>重复密码</label>
            <input type="password" name="repwd" value="" class="span4" required />
            <div class="form-actions text-center">
                <button class="btn btn-primary">提交</button>
            </div>
        </fieldset>
    </form>
</div>
