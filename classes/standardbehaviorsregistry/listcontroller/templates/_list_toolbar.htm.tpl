<div data-control="toolbar">
    {% if hasFormBehavior %}
    <a href="<?= Backend::url('{{ createUrl }}') ?>" class="btn btn-primary oc-icon-plus"><?= e(trans('backend::lang.form.create')) ?></a>
    {% endif %}
    {% if hasReorderBehavior %}
    <a href="<?= Backend::url('{{ reorderUrl }}') ?>" class="btn btn-default oc-icon-list"><?= e(trans('backend::lang.reorder.default_title')) ?></a>
    {% endif %}
    <button
        class="btn btn-default oc-icon-trash-o"
        disabled="disabled"
        onclick="$(this).data('request-data', {
            checked: $('.control-list').listWidget('getChecked')
        })"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-trigger-action="enable"
        data-trigger=".control-list input[type=checkbox]"
        data-trigger-condition="checked"
        data-request-success="$(this).prop('disabled', true)"
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>
</div>
