<?php namespace RainLab\Builder\Widgets;

use Str;
use Input;
use Backend\Classes\WidgetBase;
use RainLab\Builder\Classes\ModelModel;
use RainLab\Builder\Classes\ModelFormModel;
use RainLab\Builder\Classes\ModelListModel;

/**
 * Model list widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\CollapsableWidget;

    protected $theme;

    public $noRecordsMessage = 'rainlab.builder::lang.model.no_records';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', $this->getRenderData());
    }

    public function updateList()
    {
        return ['#'.$this->getId('plugin-model-list') => $this->makePartial('items', $this->getRenderData())];
    }

    public function refreshActivePlugin()
    {
        return ['#'.$this->getId('body') => $this->makePartial('widget-contents', $this->getRenderData())];
    }

    /*
     * Event handlers
     */

    public function onUpdate()
    {
        return $this->updateList();
    }

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        return $this->updateList();
    }

    /*
     * Methods for the internal use
     */

    protected function getData($pluginVector)
    {
        if (!$pluginVector) {
            return [];
        }

        $pluginCode = $pluginVector->pluginCodeObj;

        if (!$pluginCode) {
            return [];
        }

        $models = $this->getModelList($pluginCode);
        $searchTerm = Str::lower($this->getSearchTerm());

        // Apply the search
        //
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($models as $modelInfo) {
                if ($this->textMatchesSearch($words, $modelInfo['model']->className)) {
                    $result[] = $modelInfo;
                }
            }

            $models = $result;
        }

        return $models;
    }

    protected function getModelList($pluginCode)
    {
        $models = ModelModel::listPluginModels($pluginCode);
        $result = [];

        foreach ($models as $model) {
            $result[] = [
                'model' => $model,
                'forms' => ModelFormModel::listModelFiles($pluginCode, $model->className),
                'lists' => ModelListModel::listModelFiles($pluginCode, $model->className)
            ];
        }

        return $result;
    }

    protected function getRenderData()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();

        return [
            'pluginVector'=>$activePluginVector,
            'items'=>$this->getData($activePluginVector)
        ];
    }
}