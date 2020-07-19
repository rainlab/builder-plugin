<?php namespace RainLab\Builder\Rules;

use Lang;
use Illuminate\Contracts\Validation\Rule;

/**
 * Reserved keyword rule.
 *
 * Validates for the use of any PHP-reserved keywords or constants, as specified from the PHP Manual
 * http://php.net/manual/en/reserved.keywords.php
 * http://php.net/manual/en/reserved.other-reserved-words.php
 */
class Reserved implements Rule
{
    protected $reserved = [
        '__class__',
        '__dir__',
        '__file__',
        '__function__',
        '__halt_compiler',
        '__line__',
        '__method__',
        '__namespace__',
        '__trait__',
        'abstract',
        'and',
        'array',
        'as',
        'bool',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'false',
        'final',
        'finally',
        'float',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'int',
        'interface',
        'isset',
        'iterable',
        'list',
        'mixed',
        'namespace',
        'new',
        'null',
        'numeric',
        'object',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'resource',
        'return',
        'static',
        'string',
        'switch',
        'throw',
        'trait',
        'true',
        'try',
        'unset',
        'use',
        'var',
        'void',
        'while',
        'xor',
        'yield'
    ];

    /**
     * Validate the provided value
     *
     * @param string $attribute The attribute being tested
     * @param string $value The value being tested
     * @param array $params The parameters passed to the rule
     * @return bool
     */
    public function validate($attribute, $value, $params)
    {
        return $this->passes($attribute, $value);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !in_array(strtolower($value), $this->reserved);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('rainlab.builder::lang.validation.reserved');
    }
}
