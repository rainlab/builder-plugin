<button type="button" class="btn btn-primary oc-icon-plus" data-builder-command="plugin:cmdCreatePlugin"
    ><?= e(trans('rainlab.builder::lang.plugin.add')) ?></button>

<?php $mode = $this->getFilterMode(); ?>
<button 
    type="button"
    class="btn btn-default oc-icon-filter empty <?= $mode == 'my' ? 'on' : '' ?>"
    title="<?= e(trans('rainlab.builder::lang.plugin.filter_description')) ?>"
    data-toggle="tooltip"
    data-container="body"
    data-placement="bottom"
    data-stripe-load-indicator
    data-request="<?= $this->getEventHandler('onToggleFilter') ?>"
    onclick="$(this).tooltip('hide')"></button>