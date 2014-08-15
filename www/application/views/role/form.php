<form class="form-horizontal" action="<?= site_url('/admin/role/save') ?>" method="POST" onsubmit="submitFormData(this); return false;">
    <?php if ( ! empty( $role ) ) : ?>
    <input type="hidden" name="id" value="<?= @$role->id ?>" />
    <?php endif ?>
    <div class="control-group">
        <label class="control-label">角色名</label>
        <div class="controls"><input type="text" name="data[name]" value="<?= @$role->name ?>" required /></div>
    </div>
    <div class="control-group">
        <label class="control-label">允许访问</label>
        <div class="controls">
            <textarea name="data[allow]" required><?= @$role->allow ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">拒绝访问</label>
        <div class="controls">
            <textarea name="data[deny]" required><?= @$role->deny ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">角色说明</label>
        <div class="controls">
            <textarea name="data[desc]"><?= @$role->desc ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button class="btn btn-primary" data-loading-text="正在提交...">提交</button>
            <button type="reset" class="btn" data-dismiss="modal" onclick="this.form.reset()">取消</button>
        </div>
    </div>
</form>
