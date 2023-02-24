<div class="flex-layout-column fill-container">
    <div class="flex-layout-row flex-layout-item stretch" data-inspector-container=".inspector-container">

        <div class="flex-layout-item stretch-constrain layout-container relative">
            <?= $this->makePartial('buildingarea') ?>
        </div>

        <?php  /*
        <div class="flex-layout-item stretch-constrain layout-container relative">

            <div class="layout-absolute builder-blueprint-importer" data-control="builder-blueprint-importer">
                <div class="control-scrollpad" data-control="scrollpad">
                    <div class="scroll-wrapper">
                        <div class="builder-blueprint-importer-workspace">
                            <?= $this->makePartial('blueprintitems', ['items' => $items]) ?>
                        </div>
                    </div>
                </div>

                <script type="text/template" data-main-menu-template>
                    <?= $this->makePartial('blueprintitem', ['item' => $emptyItem]) ?>
                </script>
            </div>

        </div>
        */ ?>

        <?php /* The next line should be a single line (:empty selector is used in CSS) */ ?>
        <div class="flex-layout-item fix relative inspector-container builder-inspector-container" data-inspector-scrollable data-inspector-live-update></div>
    </div>
</div>