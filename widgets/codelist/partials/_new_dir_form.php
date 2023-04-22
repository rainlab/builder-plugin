<?= Form::open([
    'data-request'=>$this->getEventHandler('onNewDirectory'),
    'data-request-success'=>"\$(this).trigger('close.oc.popup')",
    'data-stripe-load-indicator'=>1,
    'id'=>'asset-new-dir-popup-form'
]) ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= e(trans('cms::lang.asset.directory_popup_title')) ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            <label class="form-label"><?= e(trans('cms::lang.asset.directory_name')) ?></label>
            <input
                type="text"
                name="name"
                value=""
                class="form-control"
                autocomplete="off">
        </div>
    </div>

    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary">
            <?= e(trans('backend::lang.form.create')) ?>
        </button>
        <button
            type="button"
            class="btn btn-default"
            data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>

    <script>
        setTimeout(
            function() {
                $('#asset-new-dir-popup-form input.form-control').focus()
            }, 310
        )
    </script>
<?= Form::close() ?>
