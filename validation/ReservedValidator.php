<?php namespace RainLab\Builder\Validation;

use Illuminate\Validation\Validator;

/**
 * Reserved keyword validation.
 *
 * Validates for the use of any PHP-reserved keywords or constants, as specified from the PHP Manual
 * http://php.net/manual/en/reserved.keywords.php
 * http://php.net/manual/en/reserved.other-reserved-words.php
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

    /**
     * @param $message
     * @param $attribute
     * @param $rule
     * @param $parameters
     * @return mixed
     */
    public function replaceReserved($message, $attribute, $rule, $parameters)
    {
        return $this->replaceAttributePlaceholder(e(trans('rainlab.builder::lang.validation.reserved')), ucfirst($this->getDisplayableAttribute($attribute)));
    }
}
