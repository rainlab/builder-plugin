<?= Form::open([
    'data-builder-command'=>'imports:cmdSaveImports',
    'data-plugin-code' => $pluginCode
]) ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= __("Import Options") ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>
    <div class="modal-body">
        <?= $form->render() ?>
    </div>
    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary">
            <?= __("Import") ?>
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
