<div class="layout-absolute builder-tailor-builder-area">
    <div class="control-scrollpad" data-control="scrollpad">
        <div class="scroll-wrapper">
            <ul class="tailor-blueprint-list">
                <?php foreach ($model->blueprints as $blueprintHandle => $blueprintConfig): ?>
                    <?= $this->makePartial('blueprint', [
                        'blueprintHandle' => $blueprintHandle,
                        'blueprintConfig' => $blueprintConfig
                    ]) ?>
                <?php endforeach ?>
                <li class="add">
                    <a href="javascript:;"
                        data-hotkey="ctrl+i, cmd+i"
                        <?php /*data-builder-command="imports:cmdAddBlueprintItem"*/ ?>
                        data-control="popup"
                        data-handler="<?= $this->getEventHandler('onShowSelectBlueprintForm') ?>"
                    >
                        <i class="icon-plus-circle"></i>
                        <span class="title"><?= __("Add Blueprint") ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
