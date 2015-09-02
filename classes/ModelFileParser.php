<?php namespace RainLab\Builder\Classes;

/**
 * Parses models source files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFileParser
{
    /**
     * Retrurns the model namespace, class name and table name.
     * @param string $fileContents Specifies the file contents.
     * @return array|null Returns an array with keys 'namespace', 'class' and 'table' 
     * Returns null if the parsing fails.
     */
    public function extractModelInfoFromSource($fileContents)
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
        }

        if (!$result) {
            return null;
        }

        return $result;
    }

    protected function extractNamespace($stream)
    {
        if (!$stream->forward()) {
            return null;
        }

        if ($stream->getCurrentCode() !== T_WHITESPACE) {
            return null;
        }

        $value = $stream->getTextToSemicolon();
        if (preg_match('/^[a-zA-Z][0-9a-zA-Z_\\\\]+$/', $value)) {
            return $value;
        }

        return null;
    }

    // protected static function extractClassName(&$tokens, $startIndex)
    // {
    //     $nextToken = self::getNextToken($tokens, $startIndex);
    //     if (!$nextToken) {
    //         return null;
    //     }

    //     if (self::getTokenCode($nextToken) !== T_WHITESPACE) {
    //         return null;
    //     }

    // }
}