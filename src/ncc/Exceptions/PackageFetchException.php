<?php

    namespace ncc\Exceptions;

    use ncc\Abstracts\ExceptionCodes;
    use Throwable;

    class PackageFetchException extends \Exception
    {
        public function __construct(string $message = "", ?Throwable $previous = null)
        {
            parent::__construct($message, ExceptionCodes::PackageFetchException, $previous);
        }
    }