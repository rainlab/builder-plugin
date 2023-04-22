<input type="hidden" name="plugin_code" value="<?= e($pluginCode) ?>">

<?= $this->makePartial('toolbar') ?>
<div class="layout-row" id="code-list-container" data-alias="<?= $this->alias ?>">
    <div class="layout-cell">
        <div class="layout-relative">
           <?= $this->makePartial('files', ['data' => $data]) ?>
        </div>
    </div>
</div>
