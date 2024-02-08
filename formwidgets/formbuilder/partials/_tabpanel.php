<li class="tab-panel <?= $active ? 'active' : null ?>">
    <?= $this->makePartial('controllist', [
        'controls' => $controls, 
        'listName' => $listName
    ]) ?>
</li>