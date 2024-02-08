<div class="layout control-scrollpanel" id="builder-side-panel">
    <div class="layout-cell">
        <div class="layout-relative fix-button-container fix-button-content-header">
            <!-- Tables -->
            <form
                class="layout"
                data-content-id="database"
                data-template-type="database"
                data-type-icon="oc-icon-table"
                onsubmit="return false">
                <?= $this->widget->databaseTableList->render() ?>
            </form>

            <!-- Models -->
            <form
                class="layout hide oc-hide"
                data-content-id="models"
                data-template-type="model"
                data-type-icon="oc-icon-random"
                onsubmit="return false">
                <?= $this->widget->modelList->render() ?>
            </form>

            <!-- Controllers -->
            <form
                class="layout hide oc-hide"
                data-content-id="controllers"
                data-template-type="controller"
                data-type-icon="oc-icon-asterisk"
                onsubmit="return false">
                <?= $this->widget->controllerList->render() ?>
            </form>

            <!-- Versions -->
            <form
                class="layout hide oc-hide"
                data-content-id="version"
                data-template-type="version"
                data-type-icon="oc-icon-code-fork"
                onsubmit="return false">
                <?= $this->widget->versionList->render() ?>
            </form>

            <!-- Languages -->
            <form
                class="layout hide oc-hide"
                data-content-id="localization"
                data-template-type="localization"
                data-type-icon="oc-icon-glove"
                onsubmit="return false">
                <?= $this->widget->languageList->render() ?>
            </form>

            <!-- Code Editor -->
            <form
                class="layout hide oc-hide"
                data-content-id="code"
                data-template-type="code"
                data-type-icon="icon-file-code-o"
                onsubmit="return false">
                <?= $this->widget->codeList->render() ?>
            </form>
        </div>
    </div>
</div>
