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

    public function onDatabaseTableCreateOrOpen()
    {
        $tableName = Input::get('table_name');
        $pluginCodeObj = $this->getPluginCode();

        $widget = $this->makeBaseFormWidget($tableName);
        $this->vars['tableName'] = $tableName;

        $result = [
            'tabTitle' => $this->getTabTitle($tableName),
            'tabIcon' => 'icon-hdd-o',
            'tabId' => $this->getTabId($tableName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'tableName' => $tableName
            ])
        ];

        return $result;
    }

    public function onDatabaseTableValidateAndShowPopup()
    {
        $tableName = Input::get('table_name');

        $model = $this->loadOrCreateBaseModel($tableName);
        $model->fill($this->processColumnData($_POST));

        $pluginCode = Request::input('plugin_code');
        $model->setPluginCode($pluginCode);
        try {
            $model->validate();
        } catch (Exception $ex) {
            throw new ApplicationException($ex->getMessage());
        }

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
        $model->makeScriptFileNameUnique();

        $codeGenerator = new TableMigrationCodeGenerator();
        $model->code = $codeGenerator->wrapMigrationCode($model->scriptFileName, $model->code, $pluginCode);

        try {
            $model->save();
        }
        catch (Exception $ex) {
            throw new ApplicationException($ex->getMessage());
        }

        $result = $this->controller->widget->databaseTabelList->updateList();

        $result = array_merge(
            $result,
            $this->controller->widget->versionList->refreshActivePlugin()
        );

        $widget = $this->makeBaseFormWidget($table);
        $this->vars['tableName'] = $table;

        $result['builderResponseData'] = [
            'builderObjectName'=>$table,
            'tabId' => $this->getTabId($table),
            'tabTitle' => $table,
            'tableName' => $table,
            'operation' => $operation,
            'pluginCode' => $pluginCode->toCode(),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $this->getPluginCode()->toCode(),
                'tableName' => $table
            ])
        ];

        return $result;
    }

    public function onDatabaseTableShowDeletePopup()
    {
        $tableName = Input::get('table_name');

        $model = $this->loadOrCreateBaseModel($tableName);
        $pluginCode = Request::input('plugin_code');
        $model->setPluginCode($pluginCode);

        $migration = $model->generateDropMigration();

        return $this->makePartial('migration-popup-form', [
            'form' => $this->makeMigrationFormWidget($migration),
            'operation' => 'delete',
            'table' => $model->name,
            'pluginCode' => $pluginCode
        ]);
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

    protected function loadOrCreateBaseModel($tableName, $options = [])
    {
        $model = new DatabaseTableModel();

        if (!$tableName) {
            $model->name = $this->getPluginCode()->toDatabasePrefix().'_';

            return $model;
        }

        $model->load($tableName);
        return $model;
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

    protected function processColumnData($postData)
    {
        if (!array_key_exists('columns', $postData)) {
            return $postData;
        }

        $booleanColumns = ['unsigned', 'allow_null', 'auto_increment', 'primary_key'];
        foreach ($postData['columns'] as &$row) {
            foreach ($row as $column=>$value) {
                if (in_array($column, $booleanColumns) && $value == 'false') {
                    $row[$column] = false;
                }
            }
        }

        return $postData;
    }
}
