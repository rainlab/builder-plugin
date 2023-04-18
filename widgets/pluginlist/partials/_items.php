<?php if ($items): ?>
    <?php $activePluginCode = $this->getActivePluginCode(); ?>
    <ul>
        <?php foreach ($items as $code=>$plugin): ?>
            <li class="item <?= strcasecmp($activePluginCode, $code) === 0 ? 'active' : null ?>" data-id="<?= e($code) ?>">
                <a href="#" data-builder-command="plugin:cmdMakePluginActive" data-plugin-code="<?= e($code) ?>">
                    <i class="list-icon <?= e($plugin['icon'] )?>"></i>

                    <span class="title"><span data-localization-key="<?= e($plugin['name']) ?>" data-plugin="<?= e($code) ?>"><?= e(trans($plugin['name'])) ?></span></span>
                    <span class="description">
                        <?php if (strlen(trans($plugin['description']))): ?>
                            <span data-localization-key="<?= e($plugin['description']) ?>" data-plugin="<?= e($code) ?>"><?= e(trans($plugin['description'])) ?></span>
                        <?php else: ?>
                            <span data-localization-key="<?= e($plugin['description']) ?>" data-plugin="<?= e($code) ?>"><?= e(trans('rainlab.builder::lang.plugin.no_description')) ?></span>
                        <?php endif ?>
                    </span>

                    <span class="borders"></span>
                </a>

                <div class="controls">
                    <a
                        href="javascript:;"
                        class="control icon btn-primary oc-icon-cog" 
                        title="<?= e(trans('rainlab.builder::lang.plugin.settings')) ?>"
                        data-builder-command="plugin:cmdEditPluginSettings"
                        data-plugin-code="<?= e($code) ?>"
                    ><?= e(trans('rainlab.builder::lang.plugin.settings')) ?></a>
                </div>
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
<?php endif ?>