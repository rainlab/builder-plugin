<?php if ($items): ?>
    <ul>
        <?php 
            $pluginCode = $pluginVector->pluginCodeObj->toCode();
            foreach ($items as $controller):
                $dataId = 'controller-'.e($pluginCode).'-'.$controller;
        ?>
            <li class="item" 
                data-id="<?= e($dataId) ?>" 
            >
                <a href="#" data-builder-command="controller:cmdOpenController" data-plugin-code=<?= e($pluginCode) ?> data-id="<?= e($controller) ?>">
                    <span class="title"><?= e($controller) ?></span>
                    <span class="borders"></span>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
<?php endif ?>