<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Model;

/**
 * ModelContainer
 */
class ModelContainer extends Model
{
    /**
     * @var object blueprintDefinition
     */
    protected $blueprintDefinition;

    /**
     * getBlueprintAttribute
     */
    public function getBlueprintAttribute()
    {
        return $this->getBlueprintDefinition();
    }

    /**
     * getBlueprintDefinition
     */
    public function getBlueprintDefinition()
    {
        return $this->blueprintDefinition;
    }

    /**
     * setBlueprintDefinition
     */
    public function setBlueprintDefinition($blueprint)
    {
        return $this->blueprintDefinition = $blueprint;
    }
}
