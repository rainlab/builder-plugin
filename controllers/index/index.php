<?= Block::put('head') ?><?= Block::endPut() ?>

<?= Block::put('sidepanel') ?>
    <?php if (!$this->fatalError): ?>
        <?= $this->makePartial('sidepanel') ?>
    <?php endif ?>
<?= Block::endPut() ?>

<?= Block::put('sidepanel-flyout') ?>
    <?= $this->makePartial('plugin-selector') ?>
<?= Block::endPut() ?>

<?= Block::put('body') ?>
    <?php if (!$this->fatalError): ?>
        <div
            data-control="tab"
            data-closable
            data-close-confirmation="<?= e(trans('backend::lang.form.confirm_tab_close')) ?>"
            data-pane-classes="layout-fill-container"
            data-max-title-symbols="15"
            data-title-as-file-names="false"
            class="flex-layout-column fill-container control-tabs master-tabs fancy-layout oc-logo-transparent"
            id="builder-master-tabs">

            <div class="flex-layout-item fix">
                <div class="tabs-container">
                    <ul class="nav nav-tabs"></ul>
                </div>
            </div>
            <div class="tab-content flex-layout-item stretch relative">
            </div>

        </div>

    <?php else: ?>
        <p class="flash-message static error"><?= e(trans($this->fatalError)) ?></p>
    <?php endif ?>
<?= Block::endPut() ?>