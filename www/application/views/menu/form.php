<form class="form-horizontal" action="<?= site_url('/admin/menu/save') ?>" method="POST" onsubmit="submitFormData(this); return false;">
    <?php if (isset($menu->id)) : ?>
    <input type="hidden" name="id" value="<?= $menu->id ?>" />
    <?php endif ?>
    <div class="control-group">
        <label class="control-label">名称</label>
        <div class="controls"><input type="text" name="data[text]" value="<?= $menu->text ?>" required /></div>
    </div>
    <div class="control-group">
        <label class="control-label">上级菜单</label>
        <div class="controls"><select name="data[pid]" class="span2">
              <option value="0" >--顶级菜单--</option>
              <?php foreach($menus as $m) : ?>
              <option value="<?=$m->id?>" <?= ($m->id == $menu->pid) ?  "selected" : '' ?> ><?=$m->text ?></option>
              <?php endforeach ; ?>
        </select></div>
    </div>
    <div class="control-group">
        <label class="control-label">Url</label>
        <div class="controls">
            <input type="text" name="data[url]" value="<?= $menu->url ?>" placeholder="顶级菜单请留空" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">图标</label>
        <div class="controls">
            <textarea name="data[icon]"><?= $menu->icon ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button class="btn btn-primary" data-loading-text="正在提交...">提交</button>
            <button type="reset" class="btn" data-dismiss="modal" onclick="this.form.reset()">取消</button>
        </div>
    </div>
</form>
