<div class="layout-row min-size panel-contents">
    <div class="sidepanel-content-header">
        <?php if ($pluginVector): ?>
            <span data-localization-key="<?= e($pluginVector->getPluginName()) ?>" data-plugin="<?= e($pluginVector->pluginCodeObj->toCode()) ?>"><?= e(trans($pluginVector->getPluginName())) ?></span>
        <?php else: ?>
            <?= e(trans('rainlab.builder::lang.common.plugin_not_selected')) ?>
        <?php endif ?>
    </div>
</div>

<?php if ($pluginVector): ?>
    <?= $this->makePartial('toolbar') ?>
    <div class="layout-row">
        <div class="layout-cell">
            <div class="layout-relative">
                <?= $this->makePartial('language-list', ['items'=>$items, 'pluginVector'=>$pluginVector]) ?>
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