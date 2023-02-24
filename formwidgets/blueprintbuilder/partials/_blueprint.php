<?php
    $blueprintInfo = $this->getBlueprintInfo($blueprintHandle);
    $fieldsConfiguration = $this->propertiesToInspectorSchema($blueprintInfo['properties']);
?>
<li>
    <h4><span><?= e(Lang::get($blueprintInfo['name'])) ?></span></h4>

    <div class="blueprint-container" data-inspectable="true" data-inspector-title="<?= Lang::get($blueprintInfo['name']) ?>">
        <?= $this->renderBlueprintBody($blueprintHandle, $blueprintInfo, $blueprintConfig) ?>

        <input type="hidden" value="<?= e(json_encode($fieldsConfiguration)) ?>" data-inspector-config>
        <input type="hidden" name="blueprints[<?= e($blueprintHandle) ?>]" value="<?= e(json_encode($blueprintConfig)) ?>" data-inspector-values>
    </div>
</li>
