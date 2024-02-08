<?php namespace RainLab\Builder\Classes;

use Lang;

/**
 * StandardControlsRegistry is a utility class for registering standard backend controls.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardControlsRegistry
{
    use \RainLab\Builder\Classes\StandardControlsRegistry\HasFormUi;
    use \RainLab\Builder\Classes\StandardControlsRegistry\HasFormFields;
    use \RainLab\Builder\Classes\StandardControlsRegistry\HasFormWidgets;

    /**
     * @var object controlLibrary
     */
    protected $controlLibrary;

    /**
     * __construct
     */
    public function __construct($controlLibrary)
    {
        $this->controlLibrary = $controlLibrary;

        $this->registerControls();
    }

    /**
     * registerControls
     */
    protected function registerControls()
    {
        // UI
        $this->registerSectionControl();
        $this->registerHintControl();
        $this->registerRulerControl();
        $this->registerPartialControl();

        // Fields
        $this->registerTextControl();
        $this->registerNumberControl();
        $this->registerPasswordControl();
        $this->registerEmailControl();
        $this->registerTextareaControl();
        $this->registerDropdownControl();
        $this->registerRadioListControl();
        $this->registerBalloonSelectorControl();
        $this->registerCheckboxControl();
        $this->registerCheckboxListControl();
        $this->registerSwitchControl();

        // Widgets
        $this->registerCodeEditorWidget();
        $this->registerColorPickerWidget();
        $this->registerDataTableWidget();
        $this->registerDatepickerWidget();
        $this->registerFileUploadWidget();
        $this->registerMarkdownWidget();
        $this->registerMediaFinderWidget();
        $this->registerNestedFormWidget();
        $this->registerRecordFinderWidget();
        $this->registerRelationWidget();
        $this->registerRepeaterWidget();
        $this->registerRichEditorWidget();
        $this->registerPageFinderWidget();
        $this->registerSensitiveWidget();
        $this->registerTagListWidget();
    }
}
