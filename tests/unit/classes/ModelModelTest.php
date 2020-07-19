<?php

use RainLab\Builder\Classes\ModelModel;
use RainLab\Builder\Classes\PluginCode;

class ModelModelTest extends TestCase
{
    public function tearDown()
    {
        // Ensure cleanup for testGetModelFields
        @unlink(__DIR__.'/../../../models/MyMock.php');
    }

    public function testValidateModelClassName()
    {
        $unQualifiedClassName = 'MyClassName';
        $this->assertTrue(ModelModel::validateModelClassName($unQualifiedClassName));

        $qualifiedClassName = 'RainLab\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($qualifiedClassName));

        $fullyQualifiedClassName = '\RainLab\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($fullyQualifiedClassName));

        $qualifiedClassNameStartingWithLowerCase = 'rainLab\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($qualifiedClassNameStartingWithLowerCase));
    }

    public function testInvalidateModelClassName()
    {
        $unQualifiedClassName = 'myClassName'; // starts with lower case
        $this->assertFalse(ModelModel::validateModelClassName($unQualifiedClassName));

        $qualifiedClassName = 'MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
        $this->assertFalse(ModelModel::validateModelClassName($qualifiedClassName));

        $fullyQualifiedClassName = '\MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
        $this->assertFalse(ModelModel::validateModelClassName($fullyQualifiedClassName));
    }

    public function testGetModelFields()
    {
    // Invalid Class Name
        try {
            ModelModel::getModelFields(null, 'myClassName');
        } catch (SystemException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid model class name: myClassName');
            return;
        }

        // Directory Not Found
        $pluginCodeObj = PluginCode::createFromNamespace('MyNameSpace\MyPlugin\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));

        // Directory Found, but Class Not Found
        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));

        // Model without Table Name
        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\Settings');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'Settings'));

        // Model with Table Name
        copy(__DIR__."/../../fixtures/MyMock.php", __DIR__."/../../../models/MyMock.php");
        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\MyMock');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyMock'));
    }
}
