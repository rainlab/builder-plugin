<?php
use RainLab\Builder\Classes\FilesystemGenerator;

class FilesystemGeneratorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->cleanUp();
    }

    public function tearDown()
    {
        $this->cleanUp();
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

        $this->assertFileExists($generatedDir.'/author/plugin/plugin.php');
        $this->assertFileExists($generatedDir.'/author/plugin/classes');

        $content = file_get_contents($generatedDir.'/author/plugin/plugin.php');
        $this->assertContains('Author\Plugin', $content);
        $this->assertContains('TestClass', $content);
    }

    /**
     * @expectedException        October\Rain\Exception\SystemException
     * @expectedExceptionMessage exists
     */
    public function testDestNotExistsException()
    {
        $dir = $this->getFixturesDir('temporary/null');
        $generator = new FilesystemGenerator($dir, []);
        $generator->generate();
    }

    /**
     * @expectedException        October\Rain\Exception\ApplicationException
     * @expectedExceptionMessage exists
     */
    public function testDirExistsException()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir.'/plugin', 0777, true, true);
        $this->assertFileExists($generatedDir.'/plugin');

        $structure = [
            'plugin'
        ];

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    /**
     * @expectedException        October\Rain\Exception\ApplicationException
     * @expectedExceptionMessage exists
     */
    public function testFileExistsException()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        File::put($generatedDir.'/plugin.php', 'contents');
        $this->assertFileExists($generatedDir.'/plugin.php');

        $structure = [
            'plugin.php' => 'plugin.php.tpl'
        ];

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    /**
     * @expectedException        October\Rain\Exception\SystemException
     * @expectedExceptionMessage found
     */
    public function testTemplateNotFound()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        $structure = [
            'plugin.php' => 'null.tpl'
        ];

        $generator = new FilesystemGenerator($generatedDir, $structure);
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

    protected function cleanUp()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        File::deleteDirectory($generatedDir);
    }
}
