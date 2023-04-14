<?php namespace RainLab\Builder\Widgets;

use RainLab\Builder\Classes\BlueprintDesignTimeProviderBase;

/**
 * DefaultBlueprintDesignTimeProvider
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultBlueprintDesignTimeProvider extends BlueprintDesignTimeProviderBase
{
    /**
     * @var array defaultBlueprintClasses
     */
    protected $defaultBlueprintClasses = [
        \Tailor\Classes\Blueprint\EntryBlueprint::class => 'entry',
        \Tailor\Classes\Blueprint\StreamBlueprint::class => 'entry',
        \Tailor\Classes\Blueprint\SingleBlueprint::class => 'entry',
        \Tailor\Classes\Blueprint\StructureBlueprint::class => 'entry',
        \Tailor\Classes\Blueprint\GlobalBlueprint::class => 'global',
    ];

    /**
     * renderBlueprintBody
     * @param string $class Specifies the blueprint class to render.
     * @param array $properties Blueprint property values.
     * @param  \RainLab\Builder\FormWidgets\BlueprintBuilder $blueprintBuilder BlueprintBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderBlueprintBody($class, $properties, $blueprintBuilder)
    {
        if (!array_key_exists($class, $this->defaultBlueprintClasses)) {
            return $this->renderUnknownBlueprint($class, $properties);
        }

        $partial = $this->defaultBlueprintClasses[$class];

        return $this->makePartial('blueprint-'.$partial, [
            'properties' => $properties,
            'blueprintBuilder' => $blueprintBuilder
        ]);
    }

    /**
     * renderUnknownBlueprint
     */
    protected function renderUnknownBlueprint($class, $properties)
    {
        return $this->makePartial('blueprint-unknown', [
            'properties' => $properties,
            'class' => $class
        ]);
    }
}
