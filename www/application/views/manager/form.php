<form class="form-horizontal" action="<?= site_url('/admin/manager/save') ?>" method="POST" onsubmit="submitFormData(this); return false;">
    <?php if ($manager->id) : ?>
    <input type="hidden" name="id" value="<?= $manager->id ?>" />
    <?php endif ?>
    <?php if (empty($manager->id)) : ?>
    <div class="control-group">
        <label class="control-label">登录名</label>
        <div class="controls"><input type="text" name="data[login_name]" value="<?= $manager->login_name ?>" required /></div>
    </div>
    <?php endif ?>
    <?php if (isset($roles)) : ?>
    <div class="control-group">
        <label class="control-label">角色</label>
        <div class="controls">
        <?php $user_roles = explode(',', $manager->roles); ?>
        <?php foreach ($roles as $role) : ?>
            <label class="checkbox"><input type="checkbox" name="roles[]" value="<?= $role->id ?>" <?= in_array($role->id, $user_roles)? 'checked' : ''; ?> ><?= $role->name ?></label>
        <?php endforeach; ?>
        </div>
    </div>
    <? endif; ?>
    <div class="control-group">
        <label class="control-label">密码</label>
        <div class="controls"><input type="password" name="data[password]" value="" /></div>
    </div>
    <div class="control-group">
        <label class="control-label">重复密码</label>
        <div class="controls"><input type="password" name="repwd" value="" /></div>
    </div>
    <div class="control-group">
        <label class="control-label">是否有效</label>
        <div class="controls">
            <label class="radio inline"><input type="radio" name="data[enabled]" value="1" <?= $manager->enabled !== '0' ? 'checked' : '' ?> />是</label>
            <label class="radio inline"><input type="radio" name="data[enabled]" value="0" <?= $manager->enabled == '0' ? 'checked' : '' ?> />否</label>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button class="btn btn-primary" data-loading-text="正在提交...">提交</button>
            <button type="reset" class="btn" data-dismiss="modal" onclick="this.form.reset()">取消</button>
        </div>
    </div>
</form>
