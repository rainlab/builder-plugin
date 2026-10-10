<?php

use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Classes\PluginCode;
use October\Rain\Database\Schema\Blueprint;

/**
 * ModelModelTest covers model class name validation and model field discovery.
 */
class ModelModelTest extends PluginTestCase
{
    /**
     * tearDown removes the mock model copied into the plugin models directory.
     */
    public function tearDown(): void
    {
        File::delete($this->getMockModelPath());

        parent::tearDown();
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

    public function testGetModelFieldsWithInvalidClassName()
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Invalid model class name: myClassName');

        ModelModel::getModelFields(null, 'myClassName');
    }

    public function testGetModelFieldsWithMissingPluginDirectory()
    {
        $pluginCodeObj = PluginCode::createFromNamespace('MyNameSpace\MyPlugin\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));
    }

    public function testGetModelFieldsWithMissingModelFile()
    {
        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));
    }

    public function testGetModelFieldsWithoutTableName()
    {
        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\Settings');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'Settings'));
    }

    public function testGetModelFieldsWithTableName()
    {
        Schema::create('my_mock_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        File::copy(__DIR__.'/../../fixtures/MyMock.php', $this->getMockModelPath());

        $pluginCodeObj = PluginCode::createFromNamespace('RainLab\Builder\Models\MyMock');
        $this->assertEquals(['id', 'title'], ModelModel::getModelFields($pluginCodeObj, 'MyMock'));
    }

    /**
     * getMockModelPath returns where the mock model is copied for model file parsing.
     */
    protected function getMockModelPath()
    {
        return __DIR__.'/../../../models/MyMock.php';
    }
}
