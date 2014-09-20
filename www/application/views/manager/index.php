
<script type="text/javascript">

    // 构造数据表对象
    var dt = new DataTable("managerDataTable");

    // 设置数据源URL
    dt.setDataSource("<?= site_url('/admin/manager/query') ?>");

    // 设置表格容器ID
    dt.setContainer("#dtContainer");

    // 设置每页显示数量
    dt.setLoadCount(10);

    // 设置排序字段以及排序方向
    dt.setOrderField("id", "desc");

    // 设置表格宽度
    dt.setWidth("100%");

    // 是否支持多选
    dt.setMultiple(false);

    // 设置表格行元素双击事件，双击打开编辑窗口。
    dt.setRowDblClickEvent(function(data) {
        //PopWindow( { id: 'manager', title: '修改用户', url: '<?= site_url('/manager/editform') ?>/'+data.id, width: 300, height: 250 } )
        $(this).popModal({
            url: '<?= site_url('/admin/manager/editform') ?>/' + data.id,
            title: '修改用户'
        })

    });

    // 添加表格字段
    dt.addColumn("id", "ID", 100);
    dt.addColumn("login_name", "登录名");
    dt.addColumn("created", "创建时间", 150);
    dt.addColumn("modified", "修改时间", 150);
    dt.addColumn("enabled", "有效", 50, DataTable.DATA_HANDLE_ENABLED('<?= site_url('/admin/manager/enable') ?>'));
    dt.addColumn("#", "操作", 100, "<a class='button' href='javascript:void(0)' onclick='deleteManager($$id);' title='删除' ><i class='icon-trash'></i></a>");

    // 页面加载完成后的初始化操作
    $(function() {

        // 数据表开始获取远程数据并进行渲染。
        dt.loadData(0);

        // 设置查询表单的提交动作
        $("#queryForm").submit(function() {
            dt.setParams($(this).serialize());
            dt.loadData(0);
            return false;
        });

    });

    function deleteManager(id)
    {
        if (!confirm('确定要删除吗？'))
            return;

        $.post('<?= site_url('/admin/manager/delete') ?>/' + id, function(response) {

            if (response.status == 'ok') {
                dt.reload()
            } else {
                alert(response.data)
            }

        }, 'json')
    }

    function submitFormData(form)
    {
        if (form['data[login_name]'] && form['data[login_name]'].value == '')
            return alert('请输入登录名')

        if (form['data[login_name]'] && form['data[password]'].value == '')
            return alert('请输入密码')

        if (form['data[password]'].value != '' && form['data[password]'].value != form.repwd.value)
            return alert('重复密码输入错误')

        if (!$("[name='roles[]']:checked", form).length)
            return alert('请至少选择一个角色')

        $(".btn-primary", form).button('loading');
        $.post(form.action, $(form).serialize(), function(response) {
            if (response.status == 'ok') {
                form.reset()
                dt.reload()
            } else {
                alert(response.data)
            }

            $(".btn-primary", form).button('reset');
            $('.modal:visible').modal('hide')
        }, 'json')
    }
</script>

<div id="container" class="container" style="min-width: 940px">
    <form class="form-inline" id="queryForm" action="" method="post">
        <label>登录名：<input type="text" class="input-medium" name="search[login_name:like]" /></label>
        <button type="submit" class='btn'><i class="icon-search"></i> 查询</button>
        <div class="btn-group pull-right">
            <a class="btn" title="添加用户" href="/admin/manager/addform" data-toggle="popmodal" data-destroy="0"><i class="icon-plus"></i><span class="text">添加</span></a>
        </div>
    </form>
    <div id="dtContainer"></div>
</div>
