<?php if (!$pluginCode): ?>
    <div class="layout-row min-size panel-contents">
        <div class="sidepanel-content-header">
            <?= e(trans('rainlab.builder::lang.common.plugin_not_selected')) ?>
        </div>
    </div>
<?php endif ?>
<?php if ($pluginCode): ?>
    <input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>">

    <?= $this->makePartial('toolbar') ?>
    <div class="layout-row" data-control="builder-code-list" id="code-list-container" data-alias="<?= $this->alias ?>">
        <div class="layout-cell">
            <div class="layout-relative">
            <?= $this->makePartial('files', ['items' => $items]) ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="layout-row">
        <div class="layout-cell">
            <div class="panel">
                <p class="no-data"><?= e(trans('rainlab.builder::lang.common.select_plugin_first')) ?></p>
            </div>
        </div>
    </div>
<?php endif ?>