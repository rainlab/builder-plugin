<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use Exception;
use Event;
use Lang;

/**
 * Manages Builder form control library.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControlLibrary
{
    use \October\Rain\Support\Traits\Singleton;

    const GROUP_STANDARD = 0;
    const GROUP_WIDGETS = 1;

    protected $controls = [];

    protected $groupedControls = null;

    /**
     * Returns a list of all known form controls grouped by control groups.
     * @param boolean $returnGrouped Indicates whether controls should be grouped in the result.
     * @return array
     */
    public function listControls($returnGrouped = true)
    {
        if ($this->groupedControls !== null) {
            return $returnGrouped ? $this->groupedControls : $this->controls;
        }

        Event::fire('pages.builder.registerControls', [$this]);

        $this->groupedControls = [
            $this->resolveControlGroupName(self::GROUP_STANDARD) => [],
            $this->resolveControlGroupName(self::GROUP_WIDGETS) => []
        ];

        foreach ($this->controls as $controlType=>$controlInfo) {
            $controlGroup = $this->resolveControlGroupName($controlInfo['group']);

            if (!array_key_exists($controlGroup, $this->groupedControls)) {
                $this->groupedControls[$controlGroup] = [];
            }

            $this->groupedControls[$controlGroup][$controlType] = $controlInfo;
        }

        return $returnGrouped ? $this->groupedControls : $this->controls;
    }

    /**
     * Returns information about a control by its code.
     * @param string $code Specifies the control code.
     * @return array Returns an associative array or null if the control is not registered.
     */
    public function getControlInfo($code)
    {
        $controls = $this->listControls(false);

        if (array_key_exists($code, $controls)) {
            return $controls[$code];
        }

        return null;
    }

    /**
     * Registers a control.
     * @param string $type Specifies the control code, for example "codeeditor".
     * @param string $name Specifies the control name, for example "Code editor".
     * @param string $description Specifies the control descritpion, can be empty.
     * @param string|integer $controlGroup Specifies the control group.
     * Control groups are used to create tabs in the Control Palette in Form Builder.
     * The group could one of the ControlLibrary::GROUP_ constants or a string.
     * @param string $icon Specifies the control icon for the Control Palette.
     * @see http://daftspunk.github.io/Font-Autumn/
     * @param array $properties Specifies the control properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * Use the getStandardProperties() of the ControlLibrary to get the standard control properties.
     * @param string $designTimeProviderClass Specifies the control design-time provider class name.
     * The class should extend RainLab\Builder\Classes\ControlDesignTimeProvider. If the class is not provided,
     * the default control design and design settings will be used.
     */
    public function registerControl($code, $name, $description, $controlGroup, $icon, $properties, $designTimeProviderClass)
    {
        if (!$designTimeProviderClass) {
            $designTimeProviderClass = 'RainLab\Builder\Widgets\DefaultControlDesignTimeProvider';
        }

        $this->controls[$code] = [
            'group' => $controlGroup,
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'properties' => $properties,
            'designTimeProvider' => $designTimeProviderClass
        ];
    }

    public function getStandardProperties($excludeProperties = [])
    {
        $result = [
            'label' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_label_title'),
                'type' => 'string'
            ],
            'span' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_span_title'),
                'type' => 'dropdown',
                'default' => 'full',
                'options' => ['left', 'right', 'full']
            ],
            'comment' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_comment_title'),
                'type' => 'string'
            ],
            'commentAbove' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_comment_above_title'),
                'type' => 'string'
            ],
            'default' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_default_title'),
                'type' => 'string'
            ],
            'cssClass' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_css_class_title'),
                'type' => 'string'
            ],
            'disabled' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_disabled_title'),
                'type' => 'checkbox'
            ],
            'hidden' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_hidden_title'),
                'type' => 'checkbox'
            ],
            'required' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_required_title'),
                'type' => 'checkbox'
            ]
        ];

        foreach ($excludeProperties as $property) {
            if (array_key_exists($property, $result)) {
                unset($result[$property]);
            }
        }

        return $result;
    }

    protected function resolveControlGroupName($group)
    {
        if ($group == self::GROUP_STANDARD) {
            return Lang::get('rainlab.builder::lang.form.control_group_standard');
        }

        if ($group == self::GROUP_WIDGETS) {
            return Lang::get('rainlab.builder::lang.form.control_group_widgets');
        }

        return Lang::get($group);
    }
}