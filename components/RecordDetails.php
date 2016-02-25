<?php namespace RainLab\Builder\Components;

use Lang;
use Cms\Classes\ComponentBase;
use RainLab\Builder\Classes\ComponentHelper;;
use SystemException;

class RecordDetails extends ComponentBase
{
    /**
     * A model instance to display
     * @var \October\Rain\Database\Model
     */
    public $record = null;
    
    /**
     * Message to display if the record is not found.
     * @var string
     */
    public $notFoundMessage;
    
    /**
     * Model column to display on the details page.
     * @var string
     */
    public $displayColumn;
    
    /**
     * Model column to use as a record identifier for fetching the record from the database.
     * @var string
     */
    public $modelKeyColumn;
    
    /**
     * Identifier value to load the record from the database.
     * @var string
     */
    public $identifierValue;

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.builder::lang.components.details_title',
            'description' => 'rainlab.builder::lang.components.details_description'
        ];
    }

    //
    // Properties
    //

    public function defineProperties()
    {
        return [
            'modelClass' => [
                'title'       => 'rainlab.builder::lang.components.details_model',
                'type'        => 'dropdown',
                'showExternalParam' => false
            ],
            'identifierValue' => [
                'title'       => 'rainlab.builder::lang.components.details_identifier_value',
                'description' => 'rainlab.builder::lang.components.details_identifier_value_description',
                'type'        => 'string',
                'default'     => '{{ :id }}',
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.components.details_identifier_value_required')
                    ]
                ]
            ],
            'modelKeyColumn' => [
                'title'       => 'rainlab.builder::lang.components.details_key_column',
                'description' => 'rainlab.builder::lang.components.details_key_column_description',
                'type'        => 'autocomplete',
                'default'     => 'id',
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.components.details_key_column_required')
                    ]
                ],
                'showExternalParam' => false
            ],
            'displayColumn' => [
                'title'       => 'rainlab.builder::lang.components.details_display_column',
                'description' => 'rainlab.builder::lang.components.details_display_column_description',
                'type'        => 'autocomplete',
                'depends'     => ['modelClass'],
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.components.details_display_column_required')
                    ]
                ],
                'showExternalParam' => false
            ],
            'notFoundMessage' => [
                'title'       => 'rainlab.builder::lang.components.details_not_found_message',
                'description' => 'rainlab.builder::lang.components.details_not_found_message_description',
                'default'     => Lang::get('rainlab.builder::lang.components.details_not_found_message_default'),
                'type'        => 'string',
                'showExternalParam' => false
            ]
        ];
    }

    public function getModelClassOptions()
    {
        return ComponentHelper::instance()->listGlobalModels();
    }

    public function getDisplayColumnOptions()
    {
        return ComponentHelper::instance()->listModelColumnNames();
    }

    public function getModelKeyColumnOptions()
    {
        return ComponentHelper::instance()->listModelColumnNames();
    }

    //
    // Rendering and processing
    //

    public function onRun()
    {
        $this->prepareVars();

        $this->record = $this->page['record'] = $this->loadRecord();
    }

    protected function prepareVars()
    {
        $this->notFoundMessage = $this->page['notFoundMessage'] = Lang::get($this->property('notFoundMessage'));
        $this->displayColumn = $this->page['displayColumn'] = $this->property('displayColumn');
        $this->modelKeyColumn = $this->page['modelKeyColumn'] = $this->property('modelKeyColumn');
        $this->identifierValue = $this->page['identifierValue'] = $this->property('identifierValue');

        if (!strlen($this->displayColumn)) {
            throw new SystemException('The display column name is not set.');
        }

        if (!strlen($this->modelKeyColumn)) {
            throw new SystemException('The model key column name is not set.');
        }
    }

    protected function loadRecord()
    {
        if (!strlen($this->identifierValue)) {
            return;
        }

        $modelClassName = $this->property('modelClass');
        if (!strlen($modelClassName) || !class_exists($modelClassName)) {
            throw new SystemException('Invalid model class name');
        }

        $model = new $modelClassName();
        return $model->where($this->modelKeyColumn, '=', $this->identifierValue)->first();
    }
}