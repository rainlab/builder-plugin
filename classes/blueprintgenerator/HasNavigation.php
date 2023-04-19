<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Models\MenusModel;

/**
 * HasNavigation
 */
trait HasNavigation
{
    /**
     * @var array seenMenuItems is map of uuid => menu_code
     */
    protected $seenMenuItems = [];

    /**
     * validateNavigation
     */
    protected function validateNavigation()
    {
        $this->seenMenuItems = [];
        $model = $this->loadOrCreateMenusModel();
        $model->menus = array_merge($model->menus, $this->makeNavigationItems());
        $model->validate();
    }

    /**
     * generateNavigation
     */
    protected function generateNavigation()
    {
        $model = $this->loadOrCreateMenusModel();
        $model->menus = array_merge($model->menus, $this->makeNavigationItems());
        $model->save();
    }

    /**
     * makeNavigationItems
     */
    protected function makeNavigationItems()
    {
        $indexer = BlueprintIndexer::instance();

        $menus = [];

        // Primary navigation
        foreach ($this->sourceBlueprints as $blueprint) {
            $this->setBlueprintContext($blueprint);

            $primaryNav = $indexer->findPrimaryNavigation($blueprint->uuid);
            if (!$primaryNav) {
                continue;
            }

            $menuItem = $primaryNav->toBackendMenuArray();
            $menuItem['url'] = $this->getControllerUrl();
            $menuItem['code'] = $primaryNav->code;
            $menuItem['sideMenu'] = [];

            $secondaryNav = $indexer->findSecondaryNavigation($blueprint->uuid);
            if ($secondaryNav && $secondaryNav->hasPrimary) {
                $subItem = $secondaryNav->toBackendMenuArray();
                $subItem['url'] = $this->getControllerUrl();
                $subItem['code'] = $secondaryNav->code;
                $subItem['permissions'] = [$this->getConfig('permissionCode')];
                $menuItem['sideMenu'][$secondaryNav->code] = $subItem;
                $this->seenMenuItems[$blueprint->uuid] = $primaryNav->code.'||'.$secondaryNav->code;
            }

            $menus[$primaryNav->code] = $menuItem;
        }

        // Secondary navigation
        foreach ($this->sourceBlueprints as $blueprint) {
            $this->setBlueprintContext($blueprint);

            $secondaryNav = $indexer->findSecondaryNavigation($blueprint->uuid);
            if (!$secondaryNav || $secondaryNav->hasPrimary) {
                continue;
            }

            if (!$secondaryNav->parentCode || !isset($menus[$secondaryNav->parentCode])) {
                continue;
            }

            $subItem = $secondaryNav->toBackendMenuArray();
            $subItem['url'] = $this->getControllerUrl();
            $subItem['code'] = $secondaryNav->code;
            $subItem['permissions'] = [$this->getConfig('permissionCode')];
            $this->seenMenuItems[$blueprint->uuid] = $secondaryNav->parentCode.'||'.$secondaryNav->code;

            $menus[$secondaryNav->parentCode]['sideMenu'][$secondaryNav->code] = $subItem;
        }

        foreach ($menus as &$menu) {
            $parentPermissions = [];
            foreach ($menu['sideMenu'] as $item) {
                $parentPermissions = array_merge($parentPermissions, $item['permissions']);
            }
            $menu['permissions'] = $parentPermissions;
        }

        return $menus;
    }

    /**
     * loadOrCreateMenusModel
     */
    protected function loadOrCreateMenusModel()
    {
        $model = new MenusModel;

        $model->loadPlugin($this->sourceModel->getPluginCodeObj()->toCode());

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        return $model;
    }

    /**
     * getControllerUrl
     */
    protected function getControllerUrl()
    {
        return $this->sourceModel->getPluginCodeObj()->toUrl().'/'.strtolower($this->getConfig('controllerClass'));
    }

    /**
     * getActiveMenuItemCode
     */
    protected function getActiveMenuItemCode()
    {
        $uuid = $this->sourceModel->getBlueprintObject()->uuid;

        return $this->seenMenuItems[$uuid] ?? 'unknown';
    }
}
