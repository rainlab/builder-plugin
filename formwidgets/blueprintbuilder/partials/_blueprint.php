<?php
    $blueprintInfo = $this->getBlueprintInfo($blueprintClass, $blueprintHandle);
    $fieldsConfiguration = $this->propertiesToInspectorSchema($blueprintInfo['properties']);
?>
<li data-blueprint-uuid="xxx">
    <h4><span><?= e(Lang::get($blueprintInfo['name'])) ?></span></h4>

    <div class="blueprint-container" data-inspectable="true" data-inspector-title="<?= Lang::get($blueprintInfo['name']) ?>">
        <?= $this->renderBlueprintBody($blueprintClass, $blueprintInfo, $blueprintConfig) ?>

        <input type="hidden" value="<?= e(json_encode($fieldsConfiguration)) ?>" data-inspector-config>
        <input type="hidden" name="blueprints[<?= e($blueprintClass) ?>]" value="<?= e(json_encode($blueprintConfig)) ?>" data-inspector-values>
    </div>
</li>
