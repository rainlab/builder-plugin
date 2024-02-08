<div class="form-buttons loading-indicator-container">
    <a
        href="javascript:;"
        class="btn btn-primary oc-icon-check save"
        data-builder-command="modelForm:cmdSaveForm"
        data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
        data-hotkey="ctrl+s, cmd+s">
        <?= e(trans('backend::lang.form.save')) ?>
    </a>

    <a
        href="javascript:;"
        class="btn btn-primary oc-icon-magic"
        data-control="popup"
        data-handler="onModelShowAddDatabaseFieldsPopup"
        data-stripe-load-indicator
    >
        <?= e(trans('rainlab.builder::lang.form.btn_add_database_fields')) ?>
    </a>

    <button
        type="button"
        class="btn btn-default empty oc-icon-trash-o <?php if (!strlen($fileName)): ?>hide<?php endif ?>"
        data-builder-command="modelForm:cmdDeleteForm"
        data-confirm="<?= e(trans('rainlab.builder::lang.form.confirm_delete')) ?>"
        data-control="delete-button"></button>
</div>