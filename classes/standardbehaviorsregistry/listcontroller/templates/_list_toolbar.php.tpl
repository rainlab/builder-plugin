<div data-control="toolbar">
{% if hasFormBehavior %}
    <a
        href="<?= Backend::url('{{ createUrl }}') ?>"
        class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>
{% endif %}
{% if hasImportExportBehavior %}
    <a href="<?= Backend::url('{{ exportUrl }}') ?>" class="btn btn-default oc-icon-download">
        <?= __("Export") ?>
    </a>
    <a href="<?= Backend::url('{{ importUrl }}') ?>" class="btn btn-default oc-icon-upload">
        <?= __("Import") ?>
    </a>
{% endif %}
    <button
        class="btn btn-default oc-icon-trash-o"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>
</div>
