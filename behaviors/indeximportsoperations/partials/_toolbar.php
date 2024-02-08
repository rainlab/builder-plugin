<div class="form-buttons loading-indicator-container">
    <a
        href="javascript:;"
        class="btn btn-primary oc-icon-check save"
        data-builder-command="imports:cmdConfirmImports"
        data-load-indicator="<?= __("Importing") ?>"
        data-hotkey="ctrl+s, cmd+s">
        <?= __("Import") ?>
    </a>
    <a
        href="javascript:;"
        class="btn btn-default oc-icon-database"
        data-builder-command="imports:cmdMigrateDatabase"
        data-load-indicator="<?= __("Migrating Database") ?>">
        <?= __("Migrate Database") ?>
    </a>
</div>
