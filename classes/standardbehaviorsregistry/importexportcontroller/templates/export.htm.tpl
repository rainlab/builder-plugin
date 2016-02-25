<?php Block::put('breadcrumb') ?>
    <ul>
        <li><a href="<?= Backend::url('{{ controllerUrl }}') ?>">{{ controller }}</a></li>
        <li><?= e($this->pageTitle) ?></li>
    </ul>
<?php Block::endPut() ?>
<?= Form::open(['class' => 'layout']) ?>
    <div class="layout-row">
        <?= $this->exportRender() ?>
    </div>
    <div class="form-buttons">
        <button
                type="submit"
                data-control="popup"
                data-handler="onExportLoadForm"
                data-keyboard="false"
                class="btn btn-primary">
            Export records
        </button>
    </div>
<?= Form::close() ?>