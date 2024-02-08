<?= $this->makePartial('toolbar') ?>
<div class="layout-row">
    <div class="layout-cell">
        <div class="layout-relative">
            <?= $this->makePartial('plugin-list', ['items'=>$items]) ?>
        </div>
    </div>
</div>