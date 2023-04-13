<?php
    $blueprintInfo = $this->getBlueprintInfo($blueprintClass, $blueprintUuid);
    $fieldsConfiguration = $this->propertiesToInspectorSchema($blueprintInfo['properties']);
?>
<li data-blueprint-uuid="<?= e($blueprintUuid) ?>">
    <h4><span><?= e(__($blueprintInfo['name'])) ?></span></h4>

    <div class="blueprint-container" data-inspectable="true" data-inspector-title="<?= Lang::get($blueprintInfo['name']) ?>">
        <?= $this->renderBlueprintBody($blueprintClass, $blueprintInfo, $blueprintConfig) ?>

        <input type="hidden" value="<?= e(json_encode($fieldsConfiguration)) ?>" data-inspector-config>
        <input type="hidden" name="blueprints[<?= e($blueprintUuid) ?>]" value="<?= e(json_encode($blueprintConfig)) ?>" data-inspector-values>
    </div>
</li>
