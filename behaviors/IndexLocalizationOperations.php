<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\LocalizationModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin localization management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexLocalizationOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/localizationmodel/fields.yaml';

    public function onLanguageCreateOrOpen()
    {
        $language = Input::get('original_language');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($language, $options);
        $this->vars['originalLanguage'] = $language;

        if ($widget->model->isNewModel()) {
            $widget->model->initContent();
        }

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-globe',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $language),
            'isNewRecord' => $widget->model->isNewModel(),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'language' => $language
            ])
        ];

        return $result;
    }

    public function onLanguageSave()
    {
        $model = $this->loadOrCreateLocalizationFromPost();
        $model->fill($_POST);
        $model->save(false);

        Flash::success(Lang::get('rainlab.builder::lang.localization.saved'));
        $result = $this->controller->widget->languageList->updateList();

        $result['builderRepsonseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->language),
            'tabTitle' => $this->getTabName($model),
            'language' => $model->language
        ];

        return $result;
    }

    public function onLanguageDelete()
    {
        $model = $this->loadOrCreateLocalizationFromPost();

        $model->deleteModel();

        return $this->controller->widget->languageList->updateList();
    }

    protected function loadOrCreateLocalizationFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $originalLanguage = Input::get('original_language');

        return $this->loadOrCreateBaseModel($originalLanguage, $options);
    }

    protected function getTabName($model)
    {
        $pluginName = $model->getModelPluginName();

        if (!strlen($model->language)) {
            return $pluginName.'/'.Lang::get('rainlab.builder::lang.localization.tab_new_language');
        }

        return $pluginName.'/'.$model->language;
    }

    protected function getTabId($pluginCode, $language)
    {
        if (!strlen($language)) {
            return 'localization-'.$pluginCode.'-'.uniqid(time());
        }

        return 'localization-'.$pluginCode.'-'.$language;
    }

    protected function loadOrCreateBaseModel($language, $options = [])
    {
        $model = new LocalizationModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$language) {
            return $model;
        }

        $model->load($language);
        return $model;
    }
}