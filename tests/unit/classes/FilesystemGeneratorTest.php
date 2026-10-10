<?php

use RainLab\Builder\Classes\FilesystemGenerator;

/**
 * FilesystemGeneratorTest covers generating files and directories from templates.
 */
class FilesystemGeneratorTest extends PluginTestCase
{
    /**
     * setUp removes any output left over from a previous run.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();
    }

    /**
     * tearDown removes the generated output.
     */
    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    public function testGenerate()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileDoesNotExist($generatedDir);

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
        $this->assertStringContainsString('Author\Plugin', $content);
        $this->assertStringContainsString('TestClass', $content);
    }

    public function testDestNotExistsException()
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("doesn't exist");

        $dir = $this->getFixturesDir('temporary/null');
        $generator = new FilesystemGenerator($dir, []);
        $generator->generate();
    }

    public function testDirExistsException()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileDoesNotExist($generatedDir);

        File::makeDirectory($generatedDir.'/plugin', 0777, true, true);
        $this->assertFileExists($generatedDir.'/plugin');

        $structure = [
            'plugin'
        ];

        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('exists');

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    public function testFileExistsException()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileDoesNotExist($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        File::put($generatedDir.'/plugin.php', 'contents');
        $this->assertFileExists($generatedDir.'/plugin.php');

        $structure = [
            'plugin.php' => 'plugin.php.tpl'
        ];

        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('exists');

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    public function testTemplateNotFound()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileDoesNotExist($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        $structure = [
            'plugin.php' => 'null.tpl'
        ];

        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('not found');

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    /**
     * getFixturesDir returns a path inside the filesystem generator fixtures.
     */
    protected function getFixturesDir($subdir)
    {
        $result = __DIR__.'/../../fixtures/filesystemgenerator';

        if (strlen($subdir)) {
            $result .= '/'.$subdir;
        }

        return $result;
    }

    /**
     * cleanUp deletes the temporary generated directory.
     */
    protected function cleanUp()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        File::deleteDirectory($generatedDir);
    }
}
