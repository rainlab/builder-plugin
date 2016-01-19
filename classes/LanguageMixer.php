<?php namespace RainLab\Builder\Classes;

use Yaml;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;

class LanguageMixer
{
    /**
     * Merges two localization languages and return merged YAML string and indexes of added lines.
     */
    public function addStringsFromAnotherLanguage($destContents, $srcArray)
    {
        // 1. Find array keys that exists in the source array and don't exist in the destination array
        // 2. Merge the arrays recursively
        // 3. To find which lines were added:
        //    3.1 get a YAML representation of the destination array
        //    3.2 walk through the missing paths obtained in step 1 and for each path:
        //    3.3 find its path and its corresponding line in the string from 3.1.

        $result = [
            'strings' => '',
            'mismatch' => false,
            'updatedLines' => [],
        ];

        try
        {
            $destArray = Yaml::parse($destContents);
        }
        catch (Exception $ex) {
            throw new ApplicationException(sprintf('Cannot parse the YAML content: %s', $ex->getMessage()));
        }

        if (!$destArray) {
            $result['strings'] = $this->arrayToYaml($srcArray);
            return $result;
        }

        $mismatch = false;
        $missingPaths = $this->findMissingPaths($destArray, $srcArray, $mismatch);
        $mergedArray = self::arrayMergeRecursive($srcArray, $destArray);

        $destStrings = $this->arrayToYaml($mergedArray);
        $addedLines = $this->getAddedLines($destStrings, $missingPaths);

        $result['strings'] = $destStrings;
        $result['updatedLines'] = $addedLines['lines'];
        $result['mismatch'] = $mismatch || $addedLines['mismatch'];

        return $result;
    }

    public static function arrayMergeRecursive(&$array1, &$array2)
    {
        // The native PHP implementation of array_merge_recursive
        // generates unexpected results when two scalar elements with a 
        // same key is found, so we use a custom one.

        $result = $array1;

        foreach ($array2 as $key=>&$value)
        {
            if (is_array ($value) && isset($result[$key]) && is_array($result[$key]))
            {
                $result[$key] = self::arrayMergeRecursive($result[$key], $value);
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function findMissingPaths($destArray, $srcArray, &$mismatch)
    {
        $result = [];
        $mismatch = false;
        $this->findMissingPathsRecursive($destArray, $srcArray, $result, [], $mismatch);

        return $result;
    }

    protected function findMissingPathsRecursive($destArray, $srcArray, &$result, $currentPath, &$mismatch)
    {
        foreach ($srcArray as $key=>$value) {
            $newPath = array_merge($currentPath, [$key]);
            $pathValue = null;
            $pathExists = $this->pathExistsInArray($destArray, $newPath, $pathValue);

            if (!$pathExists) {
                $result[] = $newPath;
            }

            if (is_array($value)) {
                $this->findMissingPathsRecursive($destArray, $value, $result, $newPath, $mismatch);
            }
            else {
                // Detect the case when the value in the destination file
                // is an array, when the value in the source file a is a string.
                if ($pathExists && is_array($pathValue)) {
                    $mismatch = true;
                }
            }
        }
    }

    protected function pathExistsInArray($array, $path, &$value)
    {
        $currentArray = $array;

        while ($path) {
            $currentPath = array_shift($path);

            if (!is_array($currentArray)) {
                return false;
            }

            if (!array_key_exists($currentPath, $currentArray)) {
                return false;
            }

            $currentArray = $currentArray[$currentPath];
        }

        $value = $currentArray;
        return true;
    }

    protected function arrayToYaml($array)
    {
        $dumper = new YamlDumper();
        return $dumper->dump($array, 20, 0, false, true);
    }

    protected function getAddedLines($strings, $paths)
    {
        $result = [
            'lines' => [],
            'mismatch' => false
        ];

        foreach ($paths as $path) {
            $line = $this->getLineForPath($strings, $path);

            if ($line !== false) {
                $result['lines'][] = $line;
            } 
            else {
                $result['mismatch'] = true;
            }
        }

        return $result;
    }

    protected function getLineForPath($strings, $path)
    {
        $strings = str_replace("\n\r", "\n", trim($strings));
        $lines = explode("\n", $strings);

        $lineCount = count($lines);
        $currentLineIndex = 0;
        foreach ($path as $indentaion=>$key) {
            $expectedKeyDefinition = str_repeat('    ', $indentaion).$key.':';

            $firstLineAfterKey = true;
            for ($lineIndex = $currentLineIndex; $lineIndex < $lineCount; $lineIndex++) {
                $line = $lines[$lineIndex];

                if (!$firstLineAfterKey) {
                    $lineIndentation = 0;
                    if (preg_match('/^\s+/', $line, $matches)) {
                        $lineIndentation = strlen($matches[0])/4;
                    }

                    if ($lineIndentation < $indentaion) {
                        continue; // Don't allow entering wrong branches
                    }
                }

                $firstLineAfterKey = false;

                if (strpos($line, $expectedKeyDefinition) === 0) {
                    $currentLineIndex = $lineIndex;
                    continue 2;
                }
            }

            // If the key wasn't found in the text, there is
            // a structure difference between the source an destination
            // languages - for example when a string key was replaced 
            // with an array of strings.
            return false; 
        }

        return $currentLineIndex;
    }
}