<div class="builder-blueprint-control-partial">
    <i class="icon-question-circle"></i><?= e(trans('rainlab.builder::lang.form.control_hint')) ?> 
    <?php 
        $path = $this->getPropertyValue($properties, 'path');
        echo strlen($path) ? ' - '.$path : null;
    ?>
</div>