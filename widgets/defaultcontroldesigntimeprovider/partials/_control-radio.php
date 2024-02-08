<div class="builder-blueprint-control-radiolist">
    <ul>
        <?php
            $defaultOptions = [
                trans('rainlab.builder::lang.form.control_radio_option_1'),
                trans('rainlab.builder::lang.form.control_radio_option_2')
            ];

            $options = (isset($properties['options']) && is_array($properties['options'])) ? $properties['options'] : $defaultOptions;

            foreach ($options as $option):
        ?>
            <li><i class="icon-circle-o"></i> <span data-localization-key="<?= e($option) ?>" data-plugin="<?= e($formBuilder->getPluginCode()) ?>"><?= e(trans($option)) ?></span></li>
        <?php endforeach ?>
    </ul>
</div>