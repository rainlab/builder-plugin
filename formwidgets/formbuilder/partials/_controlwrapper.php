<?php 
    /* DIV elements below should not have whitespace, required for CSS element:empty pseudo-class */ 
?>

<div class="control-wrapper">
    <?= $this->renderControlBody($type, $properties) ?>
</div>

<?= $this->renderControlStaticBody($type, $properties, $controlConfiguration) ?>

<input type="hidden" value="<?= e(json_encode($fieldsConfiguration)) ?>" data-inspector-config>
<input type="hidden" value="<?= e(json_encode($properties)) ?>" data-inspector-values>

<div class="remove-control" data-builder-remove-control>&times;</div>