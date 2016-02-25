<?php namespace RainLab\Builder\Classes;

/**
 * Parses controller source files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerFileParser
{
    protected $stream;

    public function __construct($fileContents)
    {
        $this->stream = new PhpSourceStream($fileContents);
    }

    public function listBehaviors()
    {
        $this->stream->reset();

        while ($this->stream->forward()) {
            $tokenCode = $this->stream->getCurrentCode();

            if ($tokenCode == T_PUBLIC) {
                $behaviors = $this->extractBehaviors();
                if ($behaviors !== false) {
                    return $behaviors;
                }
            }
        }
    }

    public function getStringPropertyValue($property)
    {
        $this->stream->reset();

        while ($this->stream->forward()) {
            $tokenCode = $this->stream->getCurrentCode();

            if ($tokenCode == T_PUBLIC) {
                $value = $this->extractPropertyValue($property);
                if ($value !== false) {
                    return $value;
                }
            }
        }
    }

    protected function extractBehaviors()
    {
        if ($this->stream->getNextExpected(T_WHITESPACE) === null) {
            return false;
        }

        if ($this->stream->getNextExpected(T_VARIABLE) === null) {
            return false;
        }

        if ($this->stream->getCurrentText() != '$implement') {
            return false;
        }

        if ($this->stream->getNextExpectedTerminated(['=', T_WHITESPACE], ['[', T_ARRAY]) === null) {
            return false;
        }

        if ($this->stream->getCurrentText() === 'array') {
            // For the array syntax 'array(' - forward to the next
            // character after the opening bracket 

            if ($this->stream->getNextExpectedTerminated(['(', T_WHITESPACE], [T_CONSTANT_ENCAPSED_STRING]) === null) {
                return false;
            }

            $this->stream->back();
        }

        $result = [];
        while ($line = $this->stream->getNextExpectedTerminated([T_CONSTANT_ENCAPSED_STRING, T_WHITESPACE], [',', ']', ')'])) {
            $line = $this->stream->unquotePhpString(trim($line));
            if (!strlen($line)) {
                continue;
            }

            $result[] = $this->normalizeBehaviorClassName($line);
        }

        return $result;
    }

    protected function extractPropertyValue($property)
    {
        if ($this->stream->getNextExpected(T_WHITESPACE) === null) {
            return false;
        }

        if ($this->stream->getNextExpected(T_VARIABLE) === null) {
            return false;
        }

        if ($this->stream->getCurrentText() != '$'.$property) {
            return false;
        }

        if ($this->stream->getNextExpectedTerminated(['=', T_WHITESPACE], [T_CONSTANT_ENCAPSED_STRING]) === null) {
            return null;
        }

        $value = trim($this->stream->getCurrentText());
        $value = $this->stream->unquotePhpString($value);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    protected function normalizeBehaviorClassName($className)
    {
        $className = str_replace('.', '\\', trim($className));
        return ltrim($className, '\\');
    }
}