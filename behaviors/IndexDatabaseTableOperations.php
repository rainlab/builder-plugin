<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\DatabaseTableModel;
use Backend\Behaviors\FormController;
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
        $dbPrefix = $this->getPluginDbPrefix();

        $widget = $this->makeBaseFormWidget($tableName);
        $widget->model->name = $dbPrefix.'_';

        $result = [
            'tabTitle' => $this->getTabTitle($tableName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginDbPrefix' => $dbPrefix
            ])
        ];

        return $result;
    }

    public function onDatabaseTableValidateAndShowPopup()
    {
        $tableName = Input::get('tableName');

        $model = $this->loadOrCreateBaseModel($tableName);
        $model->fill($_POST);

        $model->setPluginPrefix(Request::input('plugin_db_prefix'));
        $model->validate();

        $migration = $model->generateCreateOrUpdateMigration();

        return $this->makePartial('migration-popup-form', [
            'form' => $this->makeMigrationFormWidget($migration)
        ]);
    }

    protected function getTabTitle($tableName)
    {
        if (!strlen($tableName)) {
            return Lang::get('rainlab.builder::lang.database.tab_new_table');
        }

        return $tableName;
    }

    protected function loadOrCreateBaseModel($tableName)
    {
        $model = new DatabaseTableModel();

        if (!$tableName) {
            // $model->initDefaults();
            return $model;
        }

        $model->load($tableName);
        return $model;
    }

    protected function getPluginDbPrefix()
    {
        $vector = $this->controller->getBuilderActivePluginVector();

        if (!$vector) {
            throw new ApplicationException('Cannot determine the currently active plugin.');
        }

        return $vector->pluginCodeObj->toDatabasePrefix();
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