<li 
    class="item"
    data-inspectable
    data-inspector-title="<?= e(trans('rainlab.builder::lang.menu.side_menu_item')) ?>"
    draggable="true"
>
    <div class="item-container">
        <i class="<?= e($this->getItemArrayProperty($item, 'icon')) ?>"></i>
        <span data-localization-key="<?= e($this->getItemArrayProperty($item, 'label')) ?>" data-plugin="<?= e($this->getPluginCode()) ?>" class="title"><?= e(trans($this->getItemArrayProperty($item, 'label'))) ?></span>
        <a href="#" class="close-btn" data-builder-command="menus:cmdDeleteMenuItem">&times;</a>
    </div>

    <input type="hidden" value="<?= e($this->getSideMenuConfigurationSchema()) ?>" data-inspector-config>
    <input type="hidden" value="<?= e($this->getSideMenuConfiguration($item)) ?>" data-inspector-values>
</li>