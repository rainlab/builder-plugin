<?php namespace RainLab\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
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
class IndexDatabaseTableOperations extends ControllerBehavior
{
    public function onDatabaseTableCreate()
    {
        $tableName = null;

        $widget = $this->makeTableFormWidget($tableName);

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

    protected function makeTableFormWidget($tableName)
    {
// TODO: it looks like this method can be abstracted. See also Plugin Operations behavior
        $formConfig = '~/plugins/rainlab/builder/classes/databasetablemodel/fields.yaml';
        $widgetConfig = $this->makeConfig($formConfig);

        $widgetConfig->model = $this->loadOrCreateTableModel($tableName);
        $widgetConfig->alias = 'form_plugin_'.uniqid();

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = $tableName ? FormController::CONTEXT_UPDATE : FormController::CONTEXT_CREATE;

        return $form;
    }

    protected function loadOrCreateTableModel($tableName)
    {
// TODO: this method could be abstract, referred in the parent's makeTableFormWidget().
// and implemented in each behavior.
        $model = new DatabaseTableModel();

        if (!$tableName) {
            // $model->initDefaults();
            return $model;
        }

        // $model->loadTable($tableName);
        return $model;
    }
}