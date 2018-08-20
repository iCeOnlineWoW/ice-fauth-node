<?php

/**
 * Wrapper for a string; used just to be able to distinguish a string from specific class instance
 */
class TranslationWrapper
{
    /** @var string */
    public $string;

    public function __construct($str)
    {
        $this->string = $str;
    }
}
