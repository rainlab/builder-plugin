<div class="layout-absolute">
    <div class="control-scrollbar" data-control="scrollbar">
        <div class="control-codelist" id="<?= $this->getId('code-list') ?>">
            <?= $this->makePartial('items', ['items' => $items]) ?>
        </div>
    </div>
</div>
