<?php namespace RainLab\Builder\FormWidgets;

use Backend\Classes\FormWidgetBase;
use RainLab\Builder\Classes\IconList;
use Input;
use Lang;

/**
 * Menu items widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class MenuEditor extends FormWidgetBase
{
    protected $iconList = null;

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'menueditor';

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
        $this->vars['items'] = $this->model->menus;

        $this->vars['emptyItem'] = [
            'label' => Lang::get('rainlab.builder::lang.menu.new_menu_item'),
            'icon' => 'icon-life-ring',
            'code' => 'newitemcode',
            'url' => '/'
        ];

        $this->vars['emptySubItem'] = [
            'label' => Lang::get('rainlab.builder::lang.menu.new_menu_item'),
            'icon' => 'icon-sitemap',
            'code' => 'newitemcode',
            'url' => '/'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/menubuilder.js', 'builder');
    }

    public function getPluginCode()
    {
        $pluginCode = Input::get('plugin_code');
        if (strlen($pluginCode)) {
            return $pluginCode;
        }

        $pluginVector = $this->controller->getBuilderActivePluginVector();

        return $pluginVector->pluginCodeObj->toCode();
    }

    //
    // Event handlers
    //

    //
    // Methods for the internal use
    //

    protected function getItemArrayProperty($item, $property)
    {
        if (array_key_exists($property, $item)) {
            return $item[$property];
        }

        return null;
    }

    protected function getIconList()
    {
        if ($this->iconList !== null) {
            return $this->iconList;
        }

        $icons = IconList::getList();
        $this->iconList = [];

        foreach ($icons as $iconCode => $iconInfo) {
            $iconCode = preg_replace('/^oc\-/', '', $iconCode);

            $this->iconList[$iconCode] = $iconInfo;
        }

        return $this->iconList;
    }

    protected function getCommonMenuItemConfigurationSchema()
    {
        $result = [
            [
                'title' => Lang::get('rainlab.builder::lang.menu.property_code'),
                'property' => 'code',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.menu.property_code_required')
                    ]
                ]
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.property_label'),
                'type' => 'builderLocalization',
                'property' => 'label',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.menu.property_label_required')
                    ]
                ]
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.property_url'),
                'property' => 'url',
                'type' => 'autocomplete',
                'fillFrom' => 'controller-urls',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.menu.property_url_required')
                    ]
                ]
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.property_icon'),
                'property' => 'icon',
                'type' => 'dropdown',
                'options' => $this->getIconList(),
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.menu.property_icon_required')
                    ]
                ],
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.icon_svg'),
                'description' => Lang::get('rainlab.builder::lang.menu.icon_svg_description'),
                'property' => 'iconSvg',
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.property_permissions'),
                'property' => 'permissions',
                'type' => 'stringListAutocomplete',
                'fillFrom' => 'permissions'
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.counter'),
                'description' => Lang::get('rainlab.builder::lang.menu.counter_description'),
                'property' => 'counter',
                'group' => Lang::get('rainlab.builder::lang.menu.counter_group'),

            ],
            [
                'title' => Lang::get('rainlab.builder::lang.menu.counter_label'),
                'description' => Lang::get('rainlab.builder::lang.menu.counter_label_description'),
                'property' => 'counterLabel',
                'group' => Lang::get('rainlab.builder::lang.menu.counter_group'),
            ],
        ];

        return $result;
    }

    protected function getSideMenuConfigurationSchema()
    {
        $result = $this->getCommonMenuItemConfigurationSchema();

        $result[] = [
                'title' => Lang::get('rainlab.builder::lang.menu.property_attributes'),
                'property' => 'attributes',
                'type' => 'stringList'
        ];

        return json_encode($result);
    }

    protected function getSideMenuConfiguration($item)
    {
        if (!count($item)) {
            return '{}';
        }

        return json_encode($item);
    }


    protected function getMainMenuConfigurationSchema()
    {
        $result = $this->getCommonMenuItemConfigurationSchema();

        $result[] = [
            'title' => Lang::get('rainlab.builder::lang.menu.property_order'),
            'description' => Lang::get('rainlab.builder::lang.menu.property_order_description'),
            'property' => 'order',
            'validation' => [
                'regex' => [
                    'pattern' => '^[0-9]+$',
                    'message' => Lang::get('rainlab.builder::lang.menu.property_order_invalid')
                ]
            ]
        ];

        return json_encode($result);
    }

    protected function getMainMenuConfiguration($item)
    {
        if (!count($item)) {
            return '{}';
        }

        return json_encode($item);
    }
}
