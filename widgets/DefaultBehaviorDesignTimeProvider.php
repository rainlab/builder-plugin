<?php namespace RainLab\Builder\Widgets;

use RainLab\Builder\Classes\ControlDesignTimeProviderBase;
use SystemException;
use Input;
use Response;
use Request;
use Str;
use Lang;
use File;

/**
 * Default behavior design-time provider.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultBehaviorDesignTimeProvider extends BehaviorDesignTimeProviderBase
{
    protected $defaultBehaviorClasses = [
        'Backend\Behaviors\FormController' => 'form-controller',
        'Backend\Behaviors\ListController' => 'list-controller'
    ];

    /**
     * Renders behaivor body.
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Behavior property values.
     * @param  RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderBehaviorBody($class, $properties, $controllerBuilder)
    {
        if (!array_key_exists($class, $this->defaultBehaviorClasses)) {
            return $this->renderUnknownBehavior($class, $properties);
        }

        $partial = $this->defaultBehaviorClasses[$class];

        return $this->makePartial('behavior-'.$partial, [
            'properties'=>$properties,
            'controllerBuilder' => $controllerBuilder
        ]);
    }

    protected function renderUnknownControl($class, $properties)
    {
        return $this->makePartial('behavior-unknown', [
            'properties'=>$properties,
            'class'=>$class
        ]);
    }
}