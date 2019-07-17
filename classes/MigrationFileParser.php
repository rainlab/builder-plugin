<?php namespace RainLab\Builder\Classes;

/**
 * Parses migrations source files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MigrationFileParser
{
    /**
     * Returns the migration namespace and class name.
     * @param string $fileContents Specifies the file contents.
     * @return array|null Returns an array with keys 'class', 'namespace'.
     * Returns null if the parsing fails.
     */
    public function extractMigrationInfoFromSource($fileContents)
    {
        $stream = new PhpSourceStream($fileContents);

        $result = [];

        while ($stream->forward()) {
            $tokenCode = $stream->getCurrentCode();

            if ($tokenCode == T_NAMESPACE) {
                $namespace = $this->extractNamespace($stream);
                if ($namespace === null) {
                    return null;
                }

                $result['namespace'] = $namespace;
            }

            if ($tokenCode == T_CLASS) {
                $className = $this->extractClassName($stream);
                if ($className === null) {
                    return null;
                }

                $result['class'] = $className;
            }
        }

        if (!$result) {
            return null;
        }

        return $result;
    }

    protected function extractClassName($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return null;
        }

        return $stream->getNextExpectedTerminated([T_STRING], [T_WHITESPACE, ';']);
    }

    protected function extractNamespace($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return null;
        }

        return $stream->getNextExpectedTerminated([T_STRING, T_NS_SEPARATOR], [T_WHITESPACE, ';']);
    }
}
