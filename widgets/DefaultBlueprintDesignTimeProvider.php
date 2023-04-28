<?php namespace RainLab\Builder\Widgets;

use Str;
use RainLab\Builder\Classes\BlueprintDesignTimeProviderBase;
use SystemException;

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
     * @param object $blueprintObj
     * @return string Returns HTML markup string.
     */
    public function renderBlueprintBody($class, $properties, $blueprintObj)
    {
        if (!array_key_exists($class, $this->defaultBlueprintClasses)) {
            return $this->renderUnknownBlueprint($class, $properties);
        }

        $partial = $this->defaultBlueprintClasses[$class];

        return $this->makePartial('blueprint-'.$partial, [
            'properties' => $properties,
            'blueprintObj' => $blueprintObj
        ]);
    }

    /**
     * getDefaultConfiguration returns default blueprint configuration as an array.
     * @param string $class Specifies the blueprint class name.
     * @param string $blueprintObj
     * @param mixed $importsModel
     * @return array
     */
    public function getDefaultConfiguration($class, $blueprintObj, $importsModel)
    {
        if (!array_key_exists($class, $this->defaultBlueprintClasses)) {
            throw new SystemException('Unknown blueprint class: '.$class);
        }

        switch ($class) {
            case \Tailor\Classes\Blueprint\EntryBlueprint::class:
            case \Tailor\Classes\Blueprint\StreamBlueprint::class:
            case \Tailor\Classes\Blueprint\SingleBlueprint::class:
            case \Tailor\Classes\Blueprint\StructureBlueprint::class:
                return $this->getEntryBlueprintDefaultConfiguration($blueprintObj, $importsModel);
            case \Tailor\Classes\Blueprint\GlobalBlueprint::class:
                return $this->getGlobalBlueprintDefaultConfiguration($blueprintObj, $importsModel);
        }
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

    /**
     * getEntryBlueprintDefaultConfiguration
     */
    protected function getEntryBlueprintDefaultConfiguration($blueprintObj, $importsModel)
    {
        $handleBase = class_basename($blueprintObj->handle);
        $dbPrefix = $importsModel->getPluginCodeObj()->toDatabasePrefix().'_';
        $permissionPrefix = $importsModel->getPluginCodeObj()->toPermissionPrefix().'.manage_';

        $result = [
            'name' => $blueprintObj->name,
            'controllerClass' => Str::plural($handleBase),
            'modelClass' => Str::singular($handleBase),
            'tableName' => $dbPrefix . Str::snake($handleBase),
            'permissionCode' => $permissionPrefix . Str::snake($handleBase),
            'menuCode' => Str::snake($handleBase),
        ];

        return $result;
    }

    /**
     * getGlobalBlueprintDefaultConfiguration
     */
    protected function getGlobalBlueprintDefaultConfiguration($blueprintObj, $importsModel)
    {
        $handleBase = class_basename($blueprintObj->handle);
        $dbPrefix = $importsModel->getPluginCodeObj()->toDatabasePrefix().'_';
        $permissionPrefix = $importsModel->getPluginCodeObj()->toPermissionPrefix().'.manage_';

        $result = [
            'name' => $blueprintObj->name,
            'controllerClass' => Str::plural($handleBase),
            'modelClass' => Str::singular($handleBase),
            'tableName' => $dbPrefix . Str::snake($handleBase),
            'permissionCode' => $permissionPrefix . Str::snake($handleBase),
            'menuCode' => Str::snake($handleBase),
        ];

        return $result;
    }
}
