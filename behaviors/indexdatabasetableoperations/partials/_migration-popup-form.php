<?= Form::open([
    'data-builder-command'=>'databaseTable:cmdSaveMigration',
    'id'=>'builderTableMigrationPopup'
]) ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= e(trans('rainlab.builder::lang.migration.entity_name')) ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>

    <?php if (!isset($noChanges)): ?>
        <div class="modal-body">
            <?= $form->render() ?>
        </div>
        <div class="modal-footer">
            <button
                type="submit"
                class="btn btn-primary">
                <?= e(trans('rainlab.builder::lang.migration.save_and_apply')) ?>
            </button>
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="popup">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </button>
        </div>

        <input type="hidden" name="operation" value="<?= e($operation) ?>">
        <input type="hidden" name="table" value="<?= e($table) ?>">
        <input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>">
    <?php else: ?>
        <div class="modal-body">
            <p><?= e(trans('rainlab.builder::lang.migration.no_changes_to_save')) ?></p>
        </div>

        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="popup">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </button>
        </div>

        <script>
            $.oc.builder.indexController.triggerCommand('databaseTable:cmdUnModifyForm')
        </script>
    <?php endif ?>
<?= Form::close() ?>