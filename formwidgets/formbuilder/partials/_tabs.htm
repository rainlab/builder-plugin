<div data-control-list-container data-control-list-container-type="tabs" class="builder-tabs <?= e($type) ?>">
    <div class="tabs" 
        data-tab-close-confirmation="<?= e(trans('rainlab.builder::lang.form.confirm_close_tab')) ?>"
        data-tab-name-template="<?= e($tabNameTemplate) ?>"
        data-tab-already-exists="<?= e(trans('rainlab.builder::lang.form.tab_already_exists')) ?>"
    >
        <ul class="tabs">

            <?php if (!$controls): ?>
                <?= $this->makePartial('tab', ['active'=>true, 'title'=>sprintf($tabNameTemplate, '1') ]) ?>
            <?php else: ?>

                <?php 
                    $tabIndex = 0;
                    foreach ($controls as $tabName=>$tabControls): 
                        $tabIndex++;
                ?>
                    <?= $this->makePartial('tab', ['active'=>$tabIndex == 1, 'title'=>$tabName]) ?>
                <?php endforeach ?>

            <?php endif ?>

            <li class="new-tab" data-builder-new-tab></li>
        </ul>

        <ul class="panels">
            <?php if (!$controls): ?>
                <?= $this->makePartial('tabpanel', ['controls' => [], 'listName'=>$listName, 'active'=>true]); ?>
            <?php else: ?>

                <?php 
                    $tabIndex = 0;
                    foreach ($controls as $tabName=>$tabControls): 
                        $tabIndex++;
                ?>
                    <?= $this->makePartial('tabpanel', ['controls' => $tabControls, 'listName'=>$listName, 'active'=>$tabIndex == 1]); ?>
                <?php endforeach ?>

            <?php endif ?>
        </ul>

        <div class="inspector-trigger tab-control global"
            data-inspectable 
            data-inspector-title="<?= e($tabsTitle) ?>" 
            data-inspector-config="<?= e($this->getTabsConfigurationSchema()) ?>"
            data-inspector-offset-x="3"
            data-inspector-offset-y="-8"
        >
            <div>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <input data-inspector-values type="hidden" value="<?= e($this->getTabsConfigurationValues($configuration)) ?>"/>
        </div>

        <script type="text/template" data-tab-template>
            <?= $this->makePartial('tab', ['active'=>false, 'title'=>'tabtitle']) ?>
        </script>

        <script type="text/template" data-panel-template>
            <?= $this->makePartial('tabpanel', ['controls' => [], 'listName'=>$listName, 'active'=>false]) ?>
        </script>
    </div>
</div>