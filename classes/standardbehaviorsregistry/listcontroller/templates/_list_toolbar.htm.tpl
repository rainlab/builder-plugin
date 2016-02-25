<div data-control="toolbar">
    {% if hasFormBehavior %}
    <a href="<?= Backend::url('{{ createUrl }}') ?>" class="btn btn-primary oc-icon-plus"><?= e(trans('backend::lang.form.create')) ?></a>
    {% endif %}
    {% if hasReorderBehavior %}
    <a href="<?= Backend::url('{{ reorderUrl }}') ?>" class="btn btn-primary oc-icon-list"><?= e(trans('rainlab.builder::lang.controller.property_behavior_reorder')) ?></a>
    {% endif %}
    {% if hasImportExportBehavior %}
    <div class="btn-group">
        <a href="<?= Backend::url('{{ importUrl }}') ?>" class="btn btn-default oc-icon-upload"><?= e(trans('rainlab.builder::lang.controller.property_behavior_import')) ?></a>
        <a href="<?= Backend::url('{{ exportUrl }}') ?>" class="btn btn-default oc-icon-download"><?= e(trans('rainlab.builder::lang.controller.property_behavior_export')) ?></a>
    </div>
    {% endif %}
</div>