<div 
    class="control-body
        <?= $this->getPropertyValue($properties, 'disabled') ? 'field-disabled' : null ?>
        <?= $this->getPropertyValue($properties, 'hidden') ? 'field-hidden' : null ?>
    "
>
    <?php if ($hasLabels): 
        $label = $this->getPropertyValue($properties, 'label');
        $comment = $this->getPropertyValue($properties, 'oc.comment');

        // Note - the label and comment elements should not have whitespace in the markup.
    ?>
        <div class="
            builder-control-label
            <?= $this->getPropertyValue($properties, 'required') ? 'required' : null ?>
        "><?php if (strlen($label)): ?><span data-localization-key="<?= e($label) ?>" data-plugin="<?= e($this->getPluginCode()) ?>"><?= e(trans($label)) ?></span><?php endif ?></div>

        <div class="builder-control-comment-above"><?php if ($this->getPropertyValue($properties, 'oc.commentPosition') == 'above'): ?><?php if (strlen($comment)): ?><span data-localization-key="<?= e($comment) ?>" data-plugin="<?= e($this->getPluginCode()) ?>"><?= e(trans($comment)) ?></span><?php endif ?><?php endif ?></div>
    <?php endif ?>

    <?= $body ?>

    <?php if ($hasLabels): ?>
        <div class="builder-control-comment-below"><?php if ($this->getPropertyValue($properties, 'oc.commentPosition') == 'below'): ?><?php if (strlen($comment)): ?><span data-localization-key="<?= e($comment) ?>" data-plugin="<?= e($this->getPluginCode()) ?>"><?= e(trans($comment)) ?></span><?php endif ?><?php endif ?></div>
    <?php endif ?>
</div>