<div class="layout-absolute builder-controllers-builder-area">
    <div class="control-scrollpad" data-control="scrollpad">
        <div class="scroll-wrapper">
            <ul class="controller-behavior-list">
                <?php foreach ($model->behaviors as $behaviorClass=>$behaviorConfig): ?>
                    <?= $this->makePartial('behavior', ['behaviorClass'=>$behaviorClass, 'behaviorConfig'=>$behaviorConfig]) ?>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>