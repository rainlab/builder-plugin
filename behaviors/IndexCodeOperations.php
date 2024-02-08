<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\CodeFileModel;
use RainLab\Builder\Classes\PluginCode;
use Request;
use Flash;
use Input;
use Lang;

/**
 * IndexCodeOperations is plugin code management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexCodeOperations extends IndexOperationsBehaviorBase
{
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/codefilemodel/fields.yaml';

    /**
     * onCodeOpen
     */
    public function onCodeOpen()
    {
        $fileName = Input::get('fileName');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($fileName, $options);
        $this->vars['fileName'] = $fileName;

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-file-code-o',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $fileName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    /**
     * onCodeSave
     */
    public function onCodeSave()
    {
        $pluginCodeObj = new PluginCode(post('plugin_code'));
        $pluginCode = $pluginCodeObj->toCode();

        $fileName = post('fileName');

        $data = array_only(post(), ['fileName', 'content']);

        $model = $this->loadModelFromPost();
        $model->fill($data);
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.controller.saved'));

        $result = $this->controller->widget->codeList->onRefresh();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode, $fileName),
            'tabTitle' => $this->getTabName($model),
        ];

        return $result;
    }

    /**
     * getTabName
     */
    protected function getTabName($model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        return $pluginName.'/'.$model->fileName;
    }

    /**
     * getTabId
     */
    protected function getTabId($pluginCode, $fileName)
    {
        return 'code-'.$pluginCode.'-'.$fileName;
    }

    /**
     * loadModelFromPost
     */
    protected function loadModelFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $fileName = Input::get('fileName');

        return $this->loadOrCreateBaseModel($fileName, $options);
    }

    /**
     * loadOrCreateBaseModel
     */
    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new CodeFileModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$fileName) {
            if ($currentPath = $this->controller->widget->codeList->getCurrentRelativePath()) {
                $model->fileName = $currentPath . '/';
            }
            return $model;
        }

        $model->load($fileName);

        return $model;
    }
}
