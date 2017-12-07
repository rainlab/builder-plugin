<?php

use RainLab\Builder\Classes\ModelModel;

class ModelModelTest extends TestCase
{
  public function testValidateModelClassName()
  {
    $unQualifiedClassName = 'MyClassName';
    $this->assertTrue( ModelModel::validateModelClassName($unQualifiedClassName) );

    $qualifiedClassName = 'RainLab\Builder\Models\Settings';
    $this->assertTrue( ModelModel::validateModelClassName($qualifiedClassName) );

    $fullyQualifiedClassName = '\RainLab\Builder\Models\Settings';
    $this->assertTrue( ModelModel::validateModelClassName($fullyQualifiedClassName) );

    $qualifiedClassNameStartingWithLowerCase = 'rainLab\Builder\Models\Settings';
    $this->assertTrue( ModelModel::validateModelClassName($qualifiedClassNameStartingWithLowerCase) );
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
