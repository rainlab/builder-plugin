<?php

use RainLab\Builder\Models\ControllerModel;
use October\Rain\Parse\Yaml;

/**
 * ControllerGeneratorTest covers generating controllers with form designs.
 */
class ControllerGeneratorTest extends PluginTestCase
{
    /**
     * setUp creates a temporary plugin directory with a model form and list.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();

        $modelPath = $this->getPluginPath().'/models/item';
        File::makeDirectory($modelPath, 0777, true, true);
        File::put($modelPath.'/fields.yaml', "fields:\n    name:\n        label: Name\n");
        File::put($modelPath.'/columns.yaml', "columns:\n    name:\n        label: Name\n");
    }

    /**
     * tearDown removes the temporary plugin directory.
     */
    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    public function testGenerateWithBasicDesign()
    {
        $this->generateController('basic');

        $formConfig = $this->parseControllerYaml('config_form.yaml');
        $this->assertEquals(['displayMode' => 'basic'], $formConfig['design']);

        $listConfig = $this->parseControllerYaml('config_list.yaml');
        $this->assertEquals($this->getControllerUrl().'/update/:id', $listConfig['recordUrl']);
        $this->assertArrayNotHasKey('recordOnClick', $listConfig);

        $toolbar = $this->getControllerFile('_list_toolbar.php');
        $this->assertStringContainsString("Backend::url('".$this->getControllerUrl()."/create')", $toolbar);
        $this->assertStringNotContainsString('onLoadPopupForm', $toolbar);
        $this->assertStringNotContainsString('items/import', $toolbar);

        foreach (['create.php', 'update.php', 'preview.php'] as $view) {
            $this->assertStringContainsString('<?= $this->formRenderDesign() ?>', $this->getControllerFile($view));
        }
    }

    public function testGenerateWithPopupDesign()
    {
        $this->generateController('popup');

        $formConfig = $this->parseControllerYaml('config_form.yaml');
        $this->assertEquals(['displayMode' => 'popup'], $formConfig['design']);

        $listConfig = $this->parseControllerYaml('config_list.yaml');
        $this->assertEquals('popup', $listConfig['recordOnClick']);
        $this->assertArrayNotHasKey('recordUrl', $listConfig);

        $toolbar = $this->getControllerFile('_list_toolbar.php');
        $this->assertStringContainsString('data-handler="onLoadPopupForm"', $toolbar);
        $this->assertStringNotContainsString('items/create', $toolbar);
    }

    public function testGenerateWithoutDesign()
    {
        $this->generateController(null);

        $formConfig = $this->parseControllerYaml('config_form.yaml');
        $this->assertArrayNotHasKey('design', $formConfig);

        $listConfig = $this->parseControllerYaml('config_list.yaml');
        $this->assertEquals($this->getControllerUrl().'/update/:id', $listConfig['recordUrl']);

        $this->assertStringContainsString('<?= $this->formRenderDesign() ?>', $this->getControllerFile('update.php'));
    }

    public function testGenerateWithImportExportBehavior()
    {
        $this->generateController('basic', [
            \Backend\Behaviors\FormController::class,
            \Backend\Behaviors\ListController::class,
            \Backend\Behaviors\ImportExportController::class,
        ]);

        $toolbar = $this->getControllerFile('_list_toolbar.php');
        $this->assertStringContainsString("Backend::url('".$this->getControllerUrl()."/import')", $toolbar);
        $this->assertStringContainsString("Backend::url('".$this->getControllerUrl()."/export')", $toolbar);
    }

    public function testDesignIsNotWrittenWithoutFormBehavior()
    {
        $this->generateController('popup', [\Backend\Behaviors\ListController::class]);

        $this->assertFileDoesNotExist($this->getControllerPath().'/config_form.yaml');

        $listConfig = $this->parseControllerYaml('config_list.yaml');
        $this->assertArrayNotHasKey('recordOnClick', $listConfig);
        $this->assertArrayNotHasKey('recordUrl', $listConfig);

        $toolbar = $this->getControllerFile('_list_toolbar.php');
        $this->assertStringNotContainsString('onLoadPopupForm', $toolbar);
    }

    /**
     * generateController creates an Items controller in the temporary plugin.
     */
    protected function generateController($formDesign, $behaviors = null)
    {
        $model = new ControllerModel;
        $model->setPluginCode('BuilderTest.Designs');
        $model->fill([
            'controller' => 'Items',
            'baseModelClassName' => 'Item',
            'behaviors' => $behaviors ?: [
                \Backend\Behaviors\FormController::class,
                \Backend\Behaviors\ListController::class,
            ],
            'formDesign' => $formDesign,
        ]);
        $model->save();
    }

    /**
     * parseControllerYaml parses a generated controller configuration file.
     */
    protected function parseControllerYaml($fileName)
    {
        return (new Yaml)->parse($this->getControllerFile($fileName));
    }

    /**
     * getControllerFile returns the contents of a generated controller file.
     */
    protected function getControllerFile($fileName)
    {
        $path = $this->getControllerPath().'/'.$fileName;
        $this->assertFileExists($path);

        return File::get($path);
    }

    /**
     * getControllerUrl returns the backend URL of the generated controller.
     */
    protected function getControllerUrl()
    {
        return 'buildertest/designs/items';
    }

    /**
     * getControllerPath returns the generated controller files directory.
     */
    protected function getControllerPath()
    {
        return $this->getPluginPath().'/controllers/items';
    }

    /**
     * getPluginPath returns the temporary plugin directory.
     */
    protected function getPluginPath()
    {
        return $this->getAuthorPath().'/designs';
    }

    /**
     * getAuthorPath returns the temporary plugin author directory.
     */
    protected function getAuthorPath()
    {
        return plugins_path('buildertest');
    }

    /**
     * cleanUp deletes the temporary plugin directory.
     */
    protected function cleanUp()
    {
        File::deleteDirectory($this->getAuthorPath());
    }
}
