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
     * Returns the model namespace, class name and table name.
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

            if ($tokenCode == T_CLASS && !isset($result['class'])) {
                $className = $this->extractClassName($stream);
                if ($className === null) {
                    return null;
                }

                $result['class'] = $className;
            }

            if ($tokenCode == T_PUBLIC || $tokenCode == T_PROTECTED) {
                $tableName = $this->extractTableName($stream);
                if ($tableName === false) {
                    continue;
                }

                if ($tableName === null) {
                    return null;
                }

                $result['table'] = $tableName;
            }
        }

        if (!$result) {
            return null;
        }

        return $result;
    }

    /**
     * Extracts names and types of model relations.
     * @param string $fileContents Specifies the file contents.
     * @return array|null Returns an array with keys matching the relation types and values containing relation names as array.
     * Returns null if the parsing fails.
     */
    public function extractModelRelationsFromSource($fileContents)
    {
        $result = [];

        $stream = new PhpSourceStream($fileContents);

        while ($stream->forward()) {
            $tokenCode = $stream->getCurrentCode();

            if ($tokenCode == T_PUBLIC) {
                $relations = $this->extractRelations($stream);
                if ($relations === false) {
                    continue;
                }
            }
        }

        if (!$result) {
            return null;
        }

        return $result;
    }

    protected function extractNamespace($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return null;
        }

        return $stream->getNextExpectedTerminated([T_STRING, T_NS_SEPARATOR], [T_WHITESPACE, ';']);
    }

    protected function extractClassName($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return null;
        }

        return $stream->getNextExpectedTerminated([T_STRING], [T_WHITESPACE, ';']);
    }

    /**
     * Returns the table name. This method would return null in case if the
     * $table variable was found, but it value cannot be read. If the variable
     * is not found, the method returns false, allowing the outer loop to go to
     * the next token.
     */
    protected function extractTableName($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return false;
        }

        if ($stream->getNextExpected(T_VARIABLE) === null) {
            return false;
        }

        if ($stream->getCurrentText() != '$table') {
            return false;
        }

        if ($stream->getNextExpectedTerminated(['=', T_WHITESPACE], [T_CONSTANT_ENCAPSED_STRING]) === null) {
            return null;
        }

        $tableName = $stream->getCurrentText();
        $tableName = trim($tableName, '\'');
        $tableName = trim($tableName, '"');

        return $tableName;
    }

    protected function extractRelations($stream)
    {
        if ($stream->getNextExpected(T_WHITESPACE) === null) {
            return false;
        }

        if ($stream->getNextExpected(T_VARIABLE) === null) {
            return false;
        }

        $relationTypes = [
            'belongsTo',
            'belongsToMany',
            'attachMany',
            'hasMany',
            'morphToMany',
            'morphedByMany',
            'morphMany',
            'hasManyThrough'
        ];

        $relationType = null;
        $currentText = $stream->getCurrentText();

        foreach ($relationTypes as $type) {
            if ($currentText == '$'.$type) {
                $relationType = $type;
                break;
            }
        }

        if (!$relationType) {
            return false;
        }

        if ($stream->getNextExpectedTerminated(['=', T_WHITESPACE], ['[']) === null) {
            return null;
        }

        // The implementation is not finished and postponed. Relation definition could
        // be quite complex and contain nested arrays.
    }
}
