<?= Form::open([
    'class' => 'layout',
    'data-change-monitor' => 'true',
    'data-window-close-confirm' => e(trans('backend::lang.form.confirm_tab_close')),
    'data-entity' => 'imports',
    'onsubmit' => 'return false'
]) ?>

    <?= $form->render() ?>

    <input type="hidden" name="operationClass" value="IndexImportsOperations" />
    <input type="hidden" name="formWidgetAlias" value="<?= e($form->alias) ?>" />
    <input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>" />

<?= Form::close() ?>
