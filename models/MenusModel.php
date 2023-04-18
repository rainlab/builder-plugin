<?php namespace RainLab\Builder\Models;

use ApplicationException;
use SystemException;
use ValidationException;
use Lang;

/**
 * MenusModel manages plugin backend menus.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MenusModel extends PluginYamlModel
{
    /**
     * @var array menus
     */
    public $menus = [];

    /**
     * @var string yamlSection
     */
    protected $yamlSection = 'navigation';

    /**
     * @var object pluginCodeObj
     */
    protected $pluginCodeObj;

    /**
     * @var array fillable
     */
    protected static $fillable = [
        'menus'
    ];

    /**
     * @var bool preserveOriginal values
     */
    protected $preserveOriginal = false;

    /**
     * modelToYamlArray converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        $fileMenus = [];

        foreach ($this->menus as $mainMenuItem) {
            $mainMenuItem = $this->trimMenuProperties($mainMenuItem);

            if (!isset($mainMenuItem['code'])) {
                throw new ApplicationException('Cannot save menus - the main menu item code should not be empty.');
            }

            if (isset($mainMenuItem['sideMenu'])) {
                $sideMenuItems = [];

                foreach ($mainMenuItem['sideMenu'] as $sideMenuItem) {
                    $sideMenuItem = $this->trimMenuProperties($sideMenuItem);

                    if (!isset($sideMenuItem['code'])) {
                        throw new ApplicationException('Cannot save menus - the side menu item code should not be empty.');
                    }

                    $code = $sideMenuItem['code'];
                    unset($sideMenuItem['code']);

                    $sideMenuItems[$code] = $sideMenuItem;
                }

                $mainMenuItem['sideMenu'] = $sideMenuItems;
            }

            $code = $mainMenuItem['code'];
            unset($mainMenuItem['code']);

            $fileMenus[$code] = $mainMenuItem;
        }

        return $fileMenus;
    }

    /**
     * validate
     */
    public function validate()
    {
        parent::validate();

        $this->validateDuplicateMenus();
    }

    /**
     * fill
     */
    public function fill(array $attributes)
    {
        if (!is_array($attributes['menus'])) {
            $attributes['menus'] = json_decode($attributes['menus'], true);

            if ($attributes['menus'] === null) {
                throw new SystemException('Cannot decode menus JSON string.');
            }
        }

        return parent::fill($attributes);
    }

    /**
     * setPluginCodeObj
     */
    public function setPluginCodeObj($pluginCodeObj)
    {
        $this->pluginCodeObj = $pluginCodeObj;
    }

    /**
     * yamlArrayToModel loads the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $fileMenus = $array;
        $menus = [];
        $index = 0;

        foreach ($fileMenus as $code => $mainMenuItem) {
            $mainMenuItem['code'] = $code;

            if (isset($mainMenuItem['sideMenu'])) {
                $sideMenuItems = [];

                foreach ($mainMenuItem['sideMenu'] as $code => $sideMenuItem) {
                    $sideMenuItem['code'] = $code;
                    $sideMenuItems[] = $sideMenuItem;
                }

                $mainMenuItem['sideMenu'] = $sideMenuItems;
            }

            $menus[] = $mainMenuItem;
        }

        $this->menus = $menus;
    }

    /**
     * trimMenuProperties
     */
    protected function trimMenuProperties($menu)
    {
        array_walk($menu, function ($value, $key) {
            if (!is_scalar($value)) {
                return $value;
            }

            return trim($value);
        });

        return $menu;
    }

    /**
     * getFilePath returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        if ($this->pluginCodeObj === null) {
            throw new SystemException('Error saving plugin menus model - the plugin code object is not set.');
        }

        return $this->pluginCodeObj->toPluginFilePath();
    }

    /**
     * validateDuplicateMenus
     */
    protected function validateDuplicateMenus()
    {
        foreach ($this->menus as $outerIndex => $mainMenuItem) {
            $mainMenuItem = $this->trimMenuProperties($mainMenuItem);

            if (!isset($mainMenuItem['code'])) {
                continue;
            }

            if ($this->codeExistsInList($outerIndex, $mainMenuItem['code'], $this->menus)) {
                throw new ValidationException([
                    'permissions' => Lang::get(
                        'rainlab.builder::lang.menu.error_duplicate_main_menu_code',
                        ['code' => $mainMenuItem['code']]
                    )
                ]);
            }

            if (isset($mainMenuItem['sideMenu'])) {
                foreach ($mainMenuItem['sideMenu'] as $innerIndex => $sideMenuItem) {
                    $sideMenuItem = $this->trimMenuProperties($sideMenuItem);

                    if (!isset($sideMenuItem['code'])) {
                        continue;
                    }

                    if ($this->codeExistsInList($innerIndex, $sideMenuItem['code'], $mainMenuItem['sideMenu'])) {
                        throw new ValidationException([
                            'permissions' => Lang::get(
                                'rainlab.builder::lang.menu.error_duplicate_side_menu_code',
                                ['code' => $sideMenuItem['code']]
                            )
                        ]);
                    }
                }
            }
        }
    }

    /**
     * codeExistsInList
     */
    protected function codeExistsInList($codeIndex, $code, $list)
    {
        foreach ($list as $index => $item) {
            if (!isset($item['code'])) {
                continue;
            }

            if ($index == $codeIndex) {
                continue;
            }

            if ($code == $item['code']) {
                return true;
            }
        }

        return false;
    }
}
