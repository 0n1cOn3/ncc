<?php

namespace ncc\ThirdParty\php_parallel_lint\php_console_color;

class InvalidStyleException extends \Exception
{
    public function __construct($styleName)
    {
        parent::__construct("Invalid style $styleName.");
    }
}
