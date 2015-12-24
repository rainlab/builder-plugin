<?php if (!$this->fatalError): ?>

    <div class="form-preview">
        <?= $this->formRenderPreview() ?>
    </div>

<?php else: ?>
    <p class="flash-message static error"><?= e($this->fatalError) ?></p>
<?php endif ?>

<p>
    <a href="<?= Backend::url('{{ controllerUrl }}') ?>" class="btn btn-default oc-icon-chevron-left">
        <?= e(trans('backend::lang.form.return_to_list')) ?>
    </a>
</p>