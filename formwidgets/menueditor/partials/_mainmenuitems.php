<ul class="builder-menu builder-main-menu">
    <?php foreach ($items as $item): ?>
        <?= $this->makePartial('mainmenuitem', ['item' => $item]) ?>
    <?php endforeach ?>

    <li class="add">
        <a href="javascript:;"
            data-hotkey="ctrl+i, cmd+i"
            data-builder-command="menus:cmdAddMainMenuItem"
        >
            <i class="icon-plus-circle"></i>
            <span class="title"><?= e(trans('rainlab.builder::lang.menu.add_main_menu_item')) ?></span>
        </a>
    </li>
</ul>