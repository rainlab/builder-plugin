<?php namespace RainLab\Builder\Widgets;

use Backend\Classes\WidgetBase;
use RainLab\Builder\Classes\ModelModel;
use RainLab\Builder\Models\Settings as PluginSettings;
use Input;
use Response;
use Request;
use Str;
use Lang;

/**
 * Model list widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;

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


    /**
     * Returns information about this widget, including name and description.
     */
    public function widgetDetails() {}

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

            foreach ($models as $model) {
                if ($this->textMatchesSearch($words, $model)) {
                    $result[] = $model;
                }
            }

            $models = $result;
        }

        return $models;
    }

    protected function getModelList($pluginCode)
    {
        $result = ModelModel::listPluginModels($pluginCode);

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