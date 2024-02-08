<?php
    $searchMode = $this->isSearchMode();
?>
<?php if (($upPath = $this->getUpPath()) !== null && !$searchMode): ?>
    <p class="parent">
        <a href="<?= $upPath ?>" data-path="<?= $upPath ?>" class="link"><?= $this->getCurrentRelativePath() ?></a>
    </p>
<?php endif ?>
<div class="list-container animate">
    <?php if ($items): ?>
        <ul class="list">
            <?php foreach ($items as $item): ?>
                <?php
                    $dataId = 'asset-'.ltrim($item->path, '/');
                ?>
                <li class="<?= $item->type ?>" <?php if ($item->editable): ?>data-editable<?php endif ?> data-item-path="<?= e(ltrim($item->path, '/')) ?>" data-item-type="asset" data-id="<?= e($dataId) ?>">
                    <a class="link" href="javascript:;" data-builder-command="code:cmdOpenCode" data-plugin-code=<?= e($this->getPluginCode()) ?> data-path="<?= e(ltrim($item->path, '/')) ?>">
                        <?= e($item->name) ?>

                        <?php if ($searchMode): ?>
                            <span class="description">
                                <?= e(dirname($item->path)) ?>
                            </span>
                        <?php endif ?>
                    </a>

                    <div class="controls">
                        <a
                            href="javascript:;"
                            class="control icon btn-primary oc-icon-terminal"
                            title="<?= e(trans('cms::lang.asset.rename')) ?>"
                            data-control="popup"
                            data-request-data="renamePath: '<?= e($item->path) ?>'"
                            data-handler="<?= $this->getEventHandler('onLoadRenamePopup') ?>"
                        ><?= e(trans('cms::lang.asset.rename')) ?></a>
                    </div>

                    <input type="hidden" name="file[<?= e($item->path) ?>]" value="0" />
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="file[<?= e($item->path) ?>]"
                            <?= $this->isItemSelected($item->path) ? 'checked' : null ?>
                            data-request="<?= $this->getEventHandler('onSelect') ?>"
                            value="1" />
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
    <?php endif ?>
</div>
