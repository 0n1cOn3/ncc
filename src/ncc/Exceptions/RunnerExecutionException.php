<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace ncc\Exceptions;

    use Exception;
    use ncc\Abstracts\ExceptionCodes;
    use Throwable;

    class RunnerExecutionException extends Exception
    {
        /**
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct(string $message = "", ?Throwable $previous = null)
        {
            parent::__construct($message, ExceptionCodes::RunnerExecutionException, $previous);
        }
    }