<?php namespace RainLab\Builder\FormWidgets;

use Backend\Classes\FormWidgetBase;
use RainLab\Builder\Classes\ControllerBehaviorLibrary;
use ApplicationException;

/**
 * Controller builder widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'controllerbuilder';

    protected $designTimeProviders = [];

    protected $behaviorInfoCache = [];

    /**
     * {@inheritDoc}
     */
    public function init()
    {
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
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/controllerbuilder.js', 'builder');
    }

    /*
     * Event handlers
     */

    //
    // Methods for the internal use
    //

    protected function getBehaviorDesignTimeProvider($providerClass)
    {
        if (array_key_exists($providerClass, $this->designTimeProviders)) {
            return $this->designTimeProviders[$providerClass];
        }

        return $this->designTimeProviders[$providerClass] = new $providerClass($this->controller);
    }

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }

    protected function propertiesToInspectorSchema($propertyConfiguration)
    {
        $result = [];

        foreach ($propertyConfiguration as $property=>$propertyData) {
            $propertyData['property'] = $property;

            $result[] = $propertyData;
        }

        return $result;
    }

    protected function getBehaviorInfo($class)
    {
        if (array_key_exists($class, $this->behaviorInfoCache)) {
            return $this->behaviorInfoCache[$class];
        }

        $library = ControllerBehaviorLibrary::instance();
        $behaviorInfo = $library->getBehaviorInfo($class);

        if (!$behaviorInfo) {
            throw new ApplicationException('The requested behavior class information is not found.');
        }

        return $this->behaviorInfoCache[$class] = $behaviorInfo;
    }

    protected function renderBehaviorBody($behaviorClass, $behaviorInfo, $behaviorConfig)
    {
       $provider = $this->getBehaviorDesignTimeProvider($behaviorInfo['designTimeProvider']);

       return $provider->renderBehaviorBody($behaviorClass, $behaviorConfig, $this);
    }
}