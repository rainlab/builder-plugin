<div class="flex-layout-column fill-container">
    <div class="flex-layout-row flex-layout-item stretch" data-inspector-container=".inspector-container">
        <div class="flex-layout-item stretch-constrain layout-container relative">
            <?= $this->makePartial('buildingarea') ?>
        </div>

        <?php /* The next line should be a single line (:empty selector is used in CSS) */ ?>
        <div class="flex-layout-item fix relative inspector-container builder-inspector-container" data-inspector-scrollable data-inspector-live-update></div>
    </div>
</div>

<script type="text/template" data-template="control-palette-popover">
    <div class="popover-head">
        <h3><?= e(trans('rainlab.builder::lang.form.click_to_add_control')) ?></h3>
        <button type="button" class="close"
            data-dismiss="popover"
            aria-hidden="true">&times;</button>
        <a href="javascript:;" class="inspector-move-to-container oc-icon-download" data-builder-command="modelForm:cmdDockControlPalette"></a>
    </div>
    <div class="popover-fixed-height" data-control-palette-controlid="%c">%s</div>
</script>

<script type="text/template" data-template="control-palette-container">
    <div class="flex-layout-column fill-container" data-control-palette-controlid="%c" data-disposable>
        <div class="flex-layout-item fix">
            <div class="inspector-header">
                <h3><?= e(trans('rainlab.builder::lang.form.click_to_add_control')) ?>
                <a href="javascript:;" class="oc-icon-external-link-square detach" data-builder-command="modelForm:cmdUndockControlPalette"></a>
                <a href="javascript:;" class="close" data-builder-command="modelForm:cmdCloseControlPalette">&times;</a>
            </div>
        </div>
        <div class="flex-layout-item stretch relative">%s</div>
    </div>
</script>