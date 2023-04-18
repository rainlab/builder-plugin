<?= Form::open([
    'data-builder-command'=>'controller:cmdCreateController',
    'data-plugin-code' => $pluginCode
]) ?>
    <div class="modal-header flex-row-reverse">
        <button type="button" class="close" data-dismiss="popup">&times;</button>
        <h4 class="modal-title"><?= e(trans('rainlab.builder::lang.controller.new_controller')) ?></h4>
    </div>
    <div class="modal-body">
        <?= $form->render() ?>
    </div>
    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary">
            <?= e(trans('backend::lang.form.ok')) ?>
        </button>
        <button
            type="button"
            class="btn btn-default"
            data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>
    <input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>">
<?= Form::close() ?>
