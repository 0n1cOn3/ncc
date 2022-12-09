<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    class HttpResponse
    {
        /**
         * The HTTP status code.
         *
         * @var int
         */
        public $StatusCode;

        /**
         * The headers returned by the server.
         *
         * @var array
         */
        public $Headers;

        /**
         * The body returned by the server.
         *
         * @var string
         */
        public $Body;

        public function __construct()
        {
            $this->StatusCode = 0;
            $this->Headers = [];
            $this->Body = '';
        }

        /**
         * Returns an array representation of the object.
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'status_code' => $this->StatusCode,
                'headers' => $this->Headers,
                'body' => $this->Body
            ];
        }
    }