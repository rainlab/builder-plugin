<?php namespace RainLab\Builder\Widgets;

use Str;
use Input;
use Backend\Classes\WidgetBase;
use RainLab\Builder\Classes\PluginVersion;
use System\Classes\VersionManager;

/**
 * Plugin version list widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class VersionList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;

    public $noRecordsMessage = 'rainlab.builder::lang.version.no_records';

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
        return ['#'.$this->getId('plugin-version-list') => $this->makePartial('items', $this->getRenderData())];
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

    protected function getRenderData()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        if (!$activePluginVector) {
            return [
                'pluginVector'=>null,
                'items' => [],
                'unappliedVersions' => []
            ];
        }

        $versionObj = new PluginVersion();
        $items = $versionObj->getPluginVersionInformation($activePluginVector->pluginCodeObj);

        $searchTerm = Str::lower($this->getSearchTerm());
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($items as $version=>$versionInfo) {
                $description = $this->getVersionDescription($versionInfo);

                if (
                    $this->textMatchesSearch($words, $version) ||
                    (strlen($description) && $this->textMatchesSearch($words, $description))
                ) {
                    $result[$version] = $versionInfo;
                }
            }

            $items = $result;
        }

        $versionManager = VersionManager::instance();
        $unappliedVersions = $versionManager->listNewVersions($activePluginVector->pluginCodeObj->toCode());
        return [
            'pluginVector'=>$activePluginVector,
            'items'=>$items,
            'unappliedVersions'=>$unappliedVersions
        ];
    }

    protected function getVersionDescription($versionInfo)
    {
        if (is_array($versionInfo)) {
            if (array_key_exists(0, $versionInfo)) {
                return $versionInfo[0];
            }
        }

        if (is_scalar($versionInfo)) {
            return $versionInfo;
        }

        return null;
    }
}