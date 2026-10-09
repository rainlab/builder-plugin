<?php
    $formFile = isset($controlConfiguration['form']) && is_string($controlConfiguration['form'])
        ? $controlConfiguration['form']
        : '';
?>
<div class="builder-blueprint-control-partial builder-nestedform-file"<?= strlen($formFile) ? '' : ' style="display: none"' ?>>
    <i class="icon-server"></i> <?= e(trans('rainlab.builder::lang.form.control_repeater')) ?> : <span data-nestedform-file-path><?= e($formFile) ?></span>
</div>
<div class="builder-form-container builder-blueprint-control-repeater control-static-contents" data-control-container data-container-name="form"<?= strlen($formFile) ? ' style="display: none"' : '' ?>>
    <?php
        $controls = [];

        if (isset($controlConfiguration['form']['fields'])) {
            $controls = $controlConfiguration['form']['fields'];
        }
    ?>

    <?= $formBuilder->renderControlList($controls) ?>
</div>
