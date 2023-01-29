<?php

    namespace ncc\Exceptions;

    use ncc\Abstracts\ExceptionCodes;
    use Throwable;

    class PackageFetchException extends \Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct(string $message = "", ?Throwable $previous = null)
        {
            parent::__construct($message, ExceptionCodes::PackageFetchException, $previous);
        }
    }