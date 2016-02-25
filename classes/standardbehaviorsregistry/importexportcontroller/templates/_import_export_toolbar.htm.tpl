{% if hasImportExportBehavior %}
<div class="btn-group">
    <a href="<?= Backend::url('{{ importUrl }}') ?>" class="btn btn-default oc-icon-upload"><?= e(trans('rainlab.builder::lang.controller.property_behavior_import')) ?></a>
    <a href="<?= Backend::url('{{ exportUrl }}') ?>" class="btn btn-default oc-icon-download"><?= e(trans('rainlab.builder::lang.controller.property_behavior_export')) ?></a>
</div>
{% endif %}