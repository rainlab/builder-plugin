<div class="layout-absolute">
    <div class="control-scrollpad" data-control="scrollpad">
        <div class="scroll-wrapper">
            <div class="control-filelist single-line" 
                data-control="filelist"
                data-ignore-item-click="true"
                id="<?= $this->getId('database-table-list') ?>"
            >
                <?= $this->makePartial('items', ['items'=>$items]) ?>
            </div>
        </div>
    </div>
</div>