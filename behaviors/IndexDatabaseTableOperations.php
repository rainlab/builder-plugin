<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\DatabaseTableModel;
use Backend\Behaviors\FormController;
use RainLab\Builder\Classes\MigrationModel;
use RainLab\Builder\Classes\TableMigrationCodeGenerator;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Input;
use Lang;

/**
 * Database table management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexDatabaseTableOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/databasetablemodel/fields.yaml';
    protected $migrationFormConfigFile = '~/plugins/rainlab/builder/classes/migrationmodel/fields.yaml';

    public function onDatabaseTableCreate()
    {
        $tableName = null;
        $pluginCodeObj = $this->getPluginCode();

        $widget = $this->makeBaseFormWidget($tableName);
        $widget->model->name = $pluginCodeObj->toDatabasePrefix().'_';

        $result = [
            'tabTitle' => $this->getTabTitle($tableName),
            'tabIcon' => 'icon-hdd-o',
            'tabId' => $this->getTabId(null),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'tableName' => null
            ])
        ];

        return $result;
    }

    public function onDatabaseTableValidateAndShowPopup()
    {
        $tableName = Input::get('table_name');

        $model = $this->loadOrCreateBaseModel($tableName);
        $model->fill($_POST);

        $pluginCode = Request::input('plugin_code');
        $model->setPluginCode($pluginCode);
        $model->validate();

        $migration = $model->generateCreateOrUpdateMigration();

        if (!$migration) {
            return $this->makePartial('migration-popup-form', [
                'noChanges' => true
            ]);
        }

        return $this->makePartial('migration-popup-form', [
            'form' => $this->makeMigrationFormWidget($migration),
            'operation' => $model->isNewModel() ? 'create' : 'update',
            'table' => $model->name,
            'pluginCode' => $pluginCode
        ]);
    }

    public function onDatabaseTableMigrationApply()
    {
        $pluginCode = new PluginCode(Request::input('plugin_code'));
        $model = new MigrationModel();
        $model->setPluginCodeObj($pluginCode);

        $model->fill($_POST);

        $operation = Input::get('operation');
        $table = Input::get('table');

        $model->scriptFileName = 'builder_table_'.$operation.'_'.$table;

        $codeGenerator = new TableMigrationCodeGenerator();
        $model->code = $codeGenerator->wrapMigrationCode($model->scriptFileName, $model->code, $pluginCode);

        $model->save();

        $result = $this->controller->widget->databaseTabelList->updateList();
        $result['builderRepsonseData'] = [
            'builderObjectName'=>$table,
            'tabId' => $this->getTabId($table),
            'tabTitle' => $table,
            'tableName' => $table
        ];

        return $result;
    }

    protected function getTabTitle($tableName)
    {
        if (!strlen($tableName)) {
            return Lang::get('rainlab.builder::lang.database.tab_new_table');
        }

        return $tableName;
    }

    protected function getTabId($tableName)
    {
        if (!strlen($tableName)) {
            return 'databaseTable-'.uniqid(time());
        }

        return 'databaseTable-'.$tableName;
    }

    protected function loadOrCreateBaseModel($tableName)
    {
        $model = new DatabaseTableModel();

        if (!$tableName) {
            return $model;
        }

        $model->load($tableName);
        return $model;
    }

    protected function getPluginCode()
    {
        // TODO: this method could be abstracted in the base behavior 

        $vector = $this->controller->getBuilderActivePluginVector();

        if (!$vector) {
            throw new ApplicationException('Cannot determine the currently active plugin.');
        }

        return $vector->pluginCodeObj;
    }

    protected function makeMigrationFormWidget($migration)
    {
        $widgetConfig = $this->makeConfig($this->migrationFormConfigFile);

        $widgetConfig->model = $migration;
        $widgetConfig->alias = 'form_migration_'.uniqid();

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = FormController::CONTEXT_CREATE;

        return $form;
    }
}