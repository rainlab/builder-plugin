<?php namespace RainLab\Builder\Validation;

use Illuminate\Validation\Validator;

/**
 * Reserved keyword validation.
 *
 * Validates for the use of any PHP-reserved keywords or constants, as specified from the PHP Manual
 * here: http://php.net/manual/en/reserved.keywords.php
 */
class ReservedValidator extends Validator
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
        'final',
        'finally',
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
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield'
    ];

    /**
     * Reserved keyword validator.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @return bool
     */
    public function validateReserved($attribute, $value, $parameters)
    {
        return !in_array(strtolower($value), $this->reserved);
    }
}
