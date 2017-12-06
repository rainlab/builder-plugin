<?php namespace RainLab\Builder\Classes;

class ModelModelTest extends \PHPUnit_Framework_TestCase
{
  public function testValidateModelClassName()
  {
    $unQualifiedClassName = 'MyClassName';
    $this->assertTrue( ModelModel::validateModelClassName($unQualifiedClassName) );

    $qualifiedClassName = 'RainLab\Builder\Models\Settings';
    $this->assertTrue( ModelModel::validateModelClassName($qualifiedClassName) );

    $fullyQualifiedClassName = '\RainLab\Builder\Models\Settings';
    $this->assertTrue( ModelModel::validateModelClassName($fullyQualifiedClassName) );
  }

  public function testInvalidateModelClassName()
  {
    $unQualifiedClassName = 'myClassName'; // starts with lower case
    $this->assertFalse( ModelModel::validateModelClassName($unQualifiedClassName) );

    $qualifiedClassName = 'MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
    $this->assertFalse( ModelModel::validateModelClassName($qualifiedClassName) );

    $fullyQualifiedClassName = '\MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
    $this->assertFalse( ModelModel::validateModelClassName($fullyQualifiedClassName) );
  }

}
