<div data-control="toolbar">
    {% if hasFormBehavior %}
    <a href="<?= Backend::url('{{ createUrl }}') ?>" class="btn btn-primary oc-icon-plus"><?= e(trans('backend::lang.form.create')) ?></a>
    {% endif %}
</div>
