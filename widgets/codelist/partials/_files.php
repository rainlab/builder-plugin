<div class="layout-absolute">
    <div class="control-scrollbar" data-control="scrollbar">
        <div class="control-codelist" id="<?= $this->getId('code-list') ?>">
            <?= $this->makePartial('items', ['items' => $data]) ?>
        </div>
    </div>
</div>
