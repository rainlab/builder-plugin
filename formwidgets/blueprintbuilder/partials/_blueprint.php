<?php
    $blueprintInfo = $this->getBlueprintInfo($blueprintUuid);
    $fieldsConfiguration = $this->propertiesToInspectorSchema($blueprintInfo['properties']);
?>
<li class="blueprint" data-blueprint-uuid="<?= e($blueprintUuid) ?>">
    <h4><span><?= e(__($blueprintInfo['name'])) ?></span></h4>

    <div class="blueprint-container" data-inspectable="true" data-inspector-title="<?= Lang::get($blueprintInfo['name']) ?>">
        <div class="blueprint-body">
            <?= $this->renderBlueprintBody($blueprintInfo, $blueprintConfig) ?>
        </div>

        <input type="hidden" value="<?= e(json_encode($fieldsConfiguration)) ?>" data-inspector-config>
        <input type="hidden" name="blueprints[<?= e($blueprintUuid) ?>]" value="<?= e(json_encode($blueprintConfig)) ?>" data-inspector-values>
    </div>

    <div class="remove-blueprint" data-builder-remove-blueprint>×</div>
</li>
