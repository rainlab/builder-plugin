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

        $parentCodes = [];

        // Primary navigation
        foreach ($this->sourceBlueprints as $blueprint) {
            $this->setBlueprintContext($blueprint);

            $primaryNav = $indexer->findPrimaryNavigation($blueprint->uuid);
            if (!$primaryNav) {
                continue;
            }

            $parentCodes[$primaryNav->code] = $blueprint->uuid;
            $menuItem = $primaryNav->toBackendMenuArray();
            $menuItem['url'] = $this->getControllerUrl();
            $menuItem['code'] = $this->getNavigationCodeForUuid($blueprint->uuid);
            $menuItem['sideMenu'] = [];

            $secondaryNav = $indexer->findSecondaryNavigation($blueprint->uuid);
            if ($secondaryNav && $secondaryNav->hasPrimary) {
                $subItem = $secondaryNav->toBackendMenuArray();
                $subItem['url'] = $this->getControllerUrl();
                $subItem['code'] = $this->getNavigationCodeForUuid($blueprint->uuid);
                $subItem['permissions'] = [$this->getConfig('permissionCode')];
                $menuItem['sideMenu'][$secondaryNav->code] = $subItem;
                $this->seenMenuItems[$blueprint->uuid] = $menuItem['code'].'||'.$subItem['code'];
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
            $subItem['code'] = $this->getNavigationCodeForUuid($blueprint->uuid);
            $subItem['permissions'] = [$this->getConfig('permissionCode')];

            $parentUuid = $parentCodes[$secondaryNav->parentCode] ?? null;
            $this->seenMenuItems[$blueprint->uuid] = $parentUuid
                ? $this->getNavigationCodeForUuid($parentUuid).'||'.$subItem['code']
                : $subItem['code'];

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
     * getNavigationCodeForUuid
     */
    protected function getNavigationCodeForUuid($uuid)
    {
        return $this->sourceModel->blueprints[$uuid]['menuCode'] ?? 'unknown';
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
