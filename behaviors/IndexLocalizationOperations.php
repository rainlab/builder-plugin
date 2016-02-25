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
                'language' => $language,
                'defaultLanguage' => LocalizationModel::getDefaultLanguage()
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

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->language),
            'tabTitle' => $this->getTabName($model),
            'language' => $model->language
        ];

        if ($model->language === LocalizationModel::getDefaultLanguage()) {
            $pluginCode = $model->getPluginCodeObj()->toCode();

            $registryData = [
                'strings' => LocalizationModel::getPluginRegistryData($pluginCode, null),
                'sections' => LocalizationModel::getPluginRegistryData($pluginCode, 'sections'),
                'pluginCode' => $pluginCode
            ];

            $result['builderResponseData']['registryData'] = $registryData;
        }

        return $result;
    }

    public function onLanguageDelete()
    {
        $model = $this->loadOrCreateLocalizationFromPost();

        $model->deleteModel();

        return $this->controller->widget->languageList->updateList();
    }

    public function onLanguageShowCopyStringsPopup()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $language = trim(Input::get('original_language'));

        $languages = LocalizationModel::listPluginLanguages($pluginCodeObj);

        if (strlen($language)) {
            $languages = array_diff($languages, [$language]);
        }

        return $this->makePartial('copy-strings-popup-form', ['languages'=>$languages]);
    }

    public function onLanguageCopyStringsFrom()
    {
        $sourceLanguage = Request::input('copy_from');
        $destinationText = Request::input('strings');

        $model = new LocalizationModel();
        $model->setPluginCode(Request::input('plugin_code'));

        $responseData = $model->copyStringsFrom($destinationText, $sourceLanguage);

        return ['builderResponseData' => $responseData];
    }

    public function onLanguageLoadAddStringForm()
    {
        return [
            'markup' => $this->makePartial('new-string-popup')
        ];
    }

    public function onLanguageCreateString()
    {
        $stringKey = trim(Request::input('key'));
        $stringValue = trim(Request::input('value'));

        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $pluginCode = $pluginCodeObj->toCode();
        $options = [
            'pluginCode' => $pluginCode
        ];

        $defaultLanguage = LocalizationModel::getDefaultLanguage();
        if (LocalizationModel::languageFileExists($pluginCode, $defaultLanguage)) {
            $model = $this->loadOrCreateBaseModel($defaultLanguage, $options);
        } 
        else {
            $model = LocalizationModel::initModel($pluginCode, $defaultLanguage);
        }

        $newStringKey = $model->createStringAndSave($stringKey, $stringValue);
        $pluginCode = $pluginCodeObj->toCode();

        return [
            'localizationData' => [
                'key' => $newStringKey,
                'value' => $stringValue
            ],
            'registryData' => [
                'strings' => LocalizationModel::getPluginRegistryData($pluginCode, null),
                'sections' => LocalizationModel::getPluginRegistryData($pluginCode, 'sections')
            ]
        ];
    }

    public function onLanguageGetStrings()
    {
        $model = $this->loadOrCreateLocalizationFromPost();

        return ['builderResponseData' => [
            'strings' => $model ? $model->strings : null
        ]];
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
        $pluginName = Lang::get($model->getModelPluginName());

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