<?php namespace RainLab\Builder\Widgets;

use Str;
use Input;
use Backend\Classes\WidgetBase;
use RainLab\Builder\Models\ControllerModel;

/**
 * ControllerList widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;

    /**
     * @var string noRecordsMessage
     */
    public $noRecordsMessage = 'rainlab.builder::lang.controller.no_records';

    /**
     * __construct
     */
    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * render the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', $this->getRenderData());
    }

    /**
     * updateList
     */
    public function updateList()
    {
        return [
            '#'.$this->getId('plugin-controller-list') => $this->makePartial('items', $this->getRenderData())
        ];
    }

    /**
     * refreshActivePlugin
     */
    public function refreshActivePlugin()
    {
        return [
            '#'.$this->getId('body') => $this->makePartial('widget-contents', $this->getRenderData())
        ];
    }

    /**
     * onUpdate
     */
    public function onUpdate()
    {
        return $this->updateList();
    }

    /**
     * onSearch
     */
    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        return $this->updateList();
    }

    /**
     * getControllerList
     */
    protected function getControllerList($pluginCode)
    {
        $result = ControllerModel::listPluginControllers($pluginCode);

        return $result;
    }

    /**
     * getRenderData
     */
    protected function getRenderData()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        if (!$activePluginVector) {
            return [
                'pluginVector' => null,
                'items' => []
            ];
        }

        $items = $this->getControllerList($activePluginVector->pluginCodeObj);

        $searchTerm = Str::lower($this->getSearchTerm());
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($items as $controller) {
                if ($this->textMatchesSearch($words, $controller)) {
                    $result[] = $controller;
                }
            }

            $items = $result;
        }

        return [
            'pluginVector' => $activePluginVector,
            'items' => $items
        ];
    }
}
