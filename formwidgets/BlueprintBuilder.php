<?php namespace RainLab\Builder\FormWidgets;

use Str;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use RainLab\Builder\Models\ImportsModel;
use Backend\Classes\FormWidgetBase;
use ApplicationException;

/**
 * BlueprintBuilder form widget
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class BlueprintBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'blueprintbuilder';

    protected $designTimeProviders = [];

    protected $blueprintInfoCache = [];

    /**
     * @var \Backend\Classes\WidgetBase selectWidget reference to the widget used for selecting a page.
     */
    protected $selectFormWidget;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        if (post('blueprintbuilder_flag')) {
            $this->getSelectFormWidget();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('body');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['model'] = $this->model;
        $this->vars['items'] = $this->model->blueprints;
        $this->vars['selectWidget'] = $this->getSelectFormWidget();
        $this->vars['pluginCode'] = $this->getPluginCode();

        $this->vars['emptyItem'] = [
            'label' => __("Add Blueprint"),
            'icon' => 'icon-life-ring',
            'code' => 'newitemcode',
            'url' => '/'
        ];
    }

    /**
     * onRefreshBlueprintContainer
     */
    public function onRefreshBlueprintContainer()
    {
        $uuid = post('blueprint_uuid');
        $blueprintInfo = $this->getBlueprintInfo($uuid);
        $blueprintConfig = (array) post('properties');

        return [
            'markup' => $this->renderBlueprintBody($blueprintInfo, $blueprintConfig),
            'blueprintUuid' => $uuid
        ];
    }

    /**
     * onShowSelectBlueprintForm
     */
    public function onShowSelectBlueprintForm()
    {
        $this->prepareVars();

        $selectedBlueprints = (array) post('blueprints') ?: [];
        if ($selectedBlueprints) {
            $model = $this->getSelectFormWidget()->getModel();
            $model->blueprints = $selectedBlueprints;
        }

        return $this->makePartial('select_blueprint_form');
    }

    /**
     * onSelectBlueprint
     */
    public function onSelectBlueprint()
    {
        $widget = $this->getSelectFormWidget();
        $data = $widget->getSaveData();
        $uuids = (array) ($data['blueprint_uuid'] ?? []);
        if (!$uuids) {
            throw new ApplicationException(__("There are no blueprints to import, please select a blueprint and try again."));
        }

        $result = [];
        $availableUuids = $this->getSelectFormWidget()->getModel()->getBlueprintUuidOptions();
        foreach ($uuids as $uuid) {
            $blueprintInfo = $this->getBlueprintInfo($uuid);
            $blueprintConfig = $this->generateBlueprintConfiguration($blueprintInfo);

            $result[] = $this->makePartial('blueprint', [
                'blueprintUuid' => $uuid,
                'blueprintConfig' => $blueprintConfig
            ]);

            unset($availableUuids[$uuid]);
        }

        $includeRelated = (bool) ($data['include_related'] ?? false);
        if ($includeRelated) {
            foreach ($uuids as $uuid) {
                $this->appendRelatedBlueprintsToOutput($uuid, $result, $availableUuids);
            }
        }

        return ['@#blueprintList' => implode(PHP_EOL, $result)];
    }

    /**
     * appendRelatedBlueprintsToOutput
     */
    protected function appendRelatedBlueprintsToOutput($parentUuid, &$result, &$available)
    {
        $library = TailorBlueprintLibrary::instance();
        $relatedUuids = $library->getRelatedBlueprintUuids($parentUuid);

        foreach ($relatedUuids as $uuid) {
            if (!isset($available[$uuid])) {
                continue;
            }

            $blueprintInfo = $this->getBlueprintInfo($uuid);
            $blueprintConfig = $this->generateBlueprintConfiguration($blueprintInfo);

            $result[] = $this->makePartial('blueprint', [
                'blueprintUuid' => $uuid,
                'blueprintConfig' => $blueprintConfig
            ]);

            unset($available[$uuid]);

            // Recursion
            $this->appendRelatedBlueprintsToOutput($uuid, $result, $available);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/blueprintbuilder.js', 'builder');
    }

    /**
     * getPluginCode
     */
    public function getPluginCode()
    {
        $pluginCode = post('plugin_code');
        if (strlen($pluginCode)) {
            return $pluginCode;
        }

        $pluginVector = $this->controller->getBuilderActivePluginVector();

        return $pluginVector->pluginCodeObj->toCode();
    }


    /**
     * getSelectFormWidget
     */
    protected function getSelectFormWidget()
    {
        if ($this->selectFormWidget) {
            return $this->selectFormWidget;
        }

        $config = $this->makeConfig('~/plugins/rainlab/builder/models/importsmodel/fields_select.yaml');
        $config->model = $this->makeImportsModelInstance();
        $config->alias = $this->alias . 'Select';
        $config->arrayName = 'BlueprintBuilder';

        $form = $this->makeWidget(\Backend\Widgets\Form::class, $config);
        $form->bindToController();

        return $this->selectFormWidget = $form;
    }

    /**
     * makeImportsModelInstance
     */
    protected function makeImportsModelInstance()
    {
        $model = new ImportsModel;
        $model->setPluginCode($this->getPluginCode());
        return $model;
    }

    //
    // Methods for the internal use
    //

    /**
     * getBlueprintDesignTimeProvider
     */
    protected function getBlueprintDesignTimeProvider($providerClass)
    {
        if (array_key_exists($providerClass, $this->designTimeProviders)) {
            return $this->designTimeProviders[$providerClass];
        }

        return $this->designTimeProviders[$providerClass] = new $providerClass($this->controller);
    }

    /**
     * getPropertyValue
     */
    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }

    /**
     * propertiesToInspectorSchema
     */
    protected function propertiesToInspectorSchema($propertyConfiguration)
    {
        $result = [];

        foreach ($propertyConfiguration as $property => $propertyData) {
            $propertyData['property'] = $property;

            $result[] = $propertyData;
        }

        return $result;
    }

    /**
     * getBlueprintInfo
     */
    protected function getBlueprintInfo($uuid)
    {
        if (array_key_exists($uuid, $this->blueprintInfoCache)) {
            return $this->blueprintInfoCache[$uuid];
        }

        $library = TailorBlueprintLibrary::instance();
        $blueprintInfo = $library->getBlueprintInfo($uuid);

        if (!$blueprintInfo) {
            throw new ApplicationException('The requested blueprint class information is not found.');
        }

        return $this->blueprintInfoCache[$uuid] = $blueprintInfo;
    }

    /**
     * renderBlueprintBody
     */
    protected function renderBlueprintBody($blueprintInfo, $blueprintConfig)
    {
        $blueprintClass = $blueprintInfo['blueprintClass'];

        $blueprintObj = $blueprintInfo['blueprintObj'];

        $provider = $this->getBlueprintDesignTimeProvider($blueprintInfo['designTimeProvider']);

        // Inspect the generated output files
        $importsModel = $this->makeImportsModelInstance();

        $importsModel->fill(post());

        $importsModel->blueprints[$blueprintObj->uuid] = $blueprintConfig;

        $inspectedOutput = $importsModel->inspect($blueprintObj);

        $blueprintConfig['inspectedOutput'] = $inspectedOutput;

        return $provider->renderBlueprintBody($blueprintClass, $blueprintConfig, $blueprintObj);
    }

    /**
     * generateBlueprintConfiguration
     */
    protected function generateBlueprintConfiguration($blueprintInfo): array
    {
        $blueprintClass = $blueprintInfo['blueprintClass'];

        $blueprintObj = $blueprintInfo['blueprintObj'];

        $provider = $this->getBlueprintDesignTimeProvider($blueprintInfo['designTimeProvider']);

        $model = $this->makeImportsModelInstance();

        return $provider->getDefaultConfiguration($blueprintClass, $blueprintObj, $model);
    }
}
