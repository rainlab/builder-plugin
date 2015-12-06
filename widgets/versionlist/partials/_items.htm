<?php if ($items): ?>
    <ul>
        <?php 

        $pluginCode = $pluginVector->pluginCodeObj->toCode();
        foreach ($items as $versionNumber=>$versionInfo):
            $dataId = 'version-'.e($pluginCode).'-'.$versionNumber;
            $description = $this->getVersionDescription($versionInfo);
            $applied = !array_key_exists($versionNumber, $unappliedVersions);
        ?>
            <li class="item with-icon" 
                data-id="<?= e($dataId) ?>"
                data-applied="<?= $applied ? 'true' : 'false' ?>" 
            >
                <a href="#" data-builder-command="version:cmdOpenVersion" data-plugin-code=<?= e($pluginCode) ?> data-id="<?= e($versionNumber) ?>">
                    <?php if ($applied): ?>
                        <i class="list-icon icon-check-square"></i>
                    <?php else: ?>
                        <i class="list-icon icon-clock-o mute"></i>
                    <?php endif ?>
                    <span class="title"><?= e($versionNumber) ?></span>
                    <?php if (strlen($description)): ?>
                        <span class="description">
                            <?= e($description) ?>
                        </span>
                    <?php endif ?>

                    <span class="borders"></span>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
<?php endif ?>