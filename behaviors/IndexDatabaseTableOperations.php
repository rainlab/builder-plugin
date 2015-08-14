<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\DatabaseTableModel;
use Backend\Behaviors\FormController;
use ApplicationException;
use Exception;
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

    public function onDatabaseTableCreate()
    {
        $tableName = null;

        $widget = $this->makeBaseFormWidget($tableName);

        $result = [
            'tabTitle' => $this->getTabTitle($tableName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget
            ])
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

    protected function loadOrCreateBaseModel($tableName)
    {
        $model = new DatabaseTableModel();

        if (!$tableName) {
            // $model->initDefaults();
            return $model;
        }

        // $model->loadTable($tableName);
        return $model;
    }
}