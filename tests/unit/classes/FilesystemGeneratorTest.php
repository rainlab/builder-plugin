<?php

use RainLab\Builder\Classes\FilesystemGenerator;


class FilesystemGeneratorTest extends TestCase 
{
    public function setUp()
    {
        parent::setUp();

        $generatedDir = $this->getFixturesDir('temporary/generated');
        File::deleteDirectory($generatedDir);
    }

    public function testGenerate()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        $structure = [
            'author',
            'author/plugin',
            'author/plugin/plugin.php' => 'plugin.php.tpl',
            'author/plugin/classes'
        ];

        $templatesDir = $this->getFixturesDir('templates');
        $generator = new FilesystemGenerator($generatedDir, $structure, $templatesDir);
        
        $variables = [
            'authorNamespace' => 'Author',
            'pluginNamespace' => 'Plugin'
        ];
        $generator->setVariables($variables);
        $generator->setVariable('className', 'TestClass');

        $generator->generate();
    }

    protected function getFixturesDir($subdir)
    {
        $result = __DIR__.'/../../fixtures/filesystemgenerator';

        if (strlen($subdir)) {
            $result .= '/'.$subdir;
        }

        return $result;
    }
}
