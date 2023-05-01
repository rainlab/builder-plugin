<div id="<?= $this->getId('popup') ?>" class="blueprintbuilder-popup">
    <?= Form::ajax($this->getEventHandler('onSelectBlueprint'), [
        'data-popup-load-indicator' => true,
    ]) ?>
        <input type="hidden" name="blueprintbuilder_flag" value="1" />
        <input type="hidden" name="formWidgetAlias" value="<?= e($this->getParentForm()->alias) ?>" />
        <input type="hidden" name="operationClass" value="IndexImportsOperations" />
        <input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>" />

        <div class="modal-header">
            <h4 class="modal-title"><?= e(__("Select Blueprint to Import")) ?></h4>
            <button type="button" class="btn-close" data-dismiss="popup"></button>
        </div>

        <div class="modal-body">
            <div class="blueprintbuilder-form" id="<?= $this->getId('selectWidget') ?>">
                <?= $selectWidget->render() ?>
            </div>
        </div>

        <div class="modal-footer">
            <button
                type="submit"
                class="btn btn-primary"
                data-control="apply-btn">
                <?= e(trans('backend::lang.form.ok')) ?>
            </button>
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="popup">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </button>
        </div>
    <?= Form::close() ?>
</div>
