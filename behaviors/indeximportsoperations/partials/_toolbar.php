<div class="form-buttons loading-indicator-container">
    <a
        href="javascript:;"
        class="btn btn-primary oc-icon-check save"
        data-builder-command="imports:cmdSaveImports"
        data-load-indicator="<?= __("Importing") ?>"
        data-confirm="<?= __("Please double check the selected blueprints. This import process creates multiple scaffold files and can be difficult to undo. When the process is complete, it will rename the blueprint files to use a backup extension (.bak) to disable them.") ?>"
        data-hotkey="ctrl+s, cmd+s">
        <?= __("Import") ?>
    </a>
</div>
