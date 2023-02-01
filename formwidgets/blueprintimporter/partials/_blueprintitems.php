<ul class="builder-imports">
    <?php foreach ($items as $item): ?>
        <?= $this->makePartial('blueprintitem', ['item' => $item]) ?>
    <?php endforeach ?>

    <li class="add">
        <a href="javascript:;"
            data-hotkey="ctrl+i, cmd+i"
            data-builder-command="imports:cmdAddBlueprintItem"
        >
            <i class="icon-plus-circle"></i>
            <span class="title"><?= __("Select Blueprint") ?></span>
        </a>
    </li>
</ul>
