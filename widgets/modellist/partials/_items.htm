<?php if ($items): ?>
    <ul>
        <?php foreach ($items as $modelInfo):
            $model = $modelInfo['model'];
            $modelForms = $modelInfo['forms'];
            $modelLists = $modelInfo['lists'];
            $dataId = 'model-'.$model->className;

            $modelGroup = $model->className;
            $formsGroup = $modelGroup.'-forms';
            $listsGroup = $modelGroup.'-lists';
            $modelGroupStatus = $this->getCollapseStatus($modelGroup);
            $formsGroupStatus = $this->getCollapseStatus($formsGroup);
            $listsGroupStatus = $this->getCollapseStatus($listsGroup);
        ?>
            <li class="group model" 
                data-id="<?= e($dataId) ?>" 
                data-status="<?= $modelGroupStatus ? 'expanded' : 'collapsed' ?>"
                data-group-id="<?= e($modelGroup) ?>"
            >
                <h4><a href="#" data-id="<?= e($model->className) ?>"><?= e($model->className) ?></a></h4>
                <ul>
                    <li class="group form"
                        data-status="<?= $formsGroupStatus ? 'expanded' : 'collapsed' ?>"
                        data-group-id="<?= e($formsGroup) ?>"
                    >
                        <h4><a href="#"><?= e(trans('rainlab.builder::lang.model.forms')) ?></a></h4>
                        <div class="controls">
                            <a
                                href="javascript:;"
                                class="control icon btn-primary oc-icon-plus" 
                                data-builder-command="modelForm:cmdCreateForm"
                                data-model-class="<?= e($model->className) ?>"
                                title="<?= e(trans('rainlab.builder::lang.model.add_form')) ?>"
                            ><?= e(trans('rainlab.builder::lang.model.add_form')) ?></a>
                        </div>

                        <ul>
                            <?php foreach ($modelForms as $modelForm):
                                $formDataId = 'modelForm-'.$model->className.'-'.$modelForm;
                            ?>
                                <li class="item"
                                    data-id="<?= e($formDataId) ?>" 
                                >
                                    <a 
                                        href="javascript:;"
                                        data-builder-command="modelForm:cmdOpenForm"
                                        data-model-class="<?= e($model->className) ?>"
                                        data-form="<?= e($modelForm) ?>"
                                        ><span class="title"><?= e($modelForm) ?></span></a>
                                </li>
                            <?php endforeach?>
                        </ul>
                    </li>
                    <li class="group list"
                        data-status="<?= $listsGroupStatus ? 'expanded' : 'collapsed' ?>"
                        data-group-id="<?= e($listsGroup) ?>"
                    >
                        <h4><a href="#"><?= e(trans('rainlab.builder::lang.model.lists')) ?></a></h4>
                        <div class="controls">
                            <a
                                href="javascript:;"
                                class="control icon btn-primary oc-icon-plus" 
                                data-builder-command="modelList:cmdCreateList"
                                data-model-class="<?= e($model->className) ?>"
                                title="<?= e(trans('rainlab.builder::lang.model.add_list')) ?>"
                            ><?= e(trans('rainlab.builder::lang.model.add_list')) ?></a>
                        </div>

                        <ul>
                            <?php foreach ($modelLists as $modelList):
                                $formDataId = 'modelList-'.$model->className.'-'.$modelList;
                            ?>
                                <li class="item"
                                    data-id="<?= e($formDataId) ?>" 
                                >
                                    <a 
                                        href="javascript:;"
                                        data-builder-command="modelList:cmdOpenList"
                                        data-model-class="<?= e($model->className) ?>"
                                        data-list="<?= e($modelList) ?>"
                                        ><span class="title"><?= e($modelList) ?></span></a>
                                </li>
                            <?php endforeach?>
                        </ul>
                    </li>
                </ul>
                
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p class="no-data"><?= e(trans($this->noRecordsMessage)) ?></p>
<?php endif ?>
