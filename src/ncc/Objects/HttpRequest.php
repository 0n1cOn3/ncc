<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Abstracts\HttpRequestType;

    class HttpRequest
    {
        /**
         * The HTTP request type.
         *
         * @var string|HttpRequestType
         */
        public $Type;

        /**
         * The URL to send the request to.
         *
         * @var string
         */
        public $Url;

        /**
         * The headers to send with the request.
         *
         * @var array
         */
        public $Headers;

        /**
         * The body to send with the request.
         *
         * @var string|null
         */
        public $Body;

        /**
         * The authentication username or password to send with the request.
         *
         * @var array|string
         */
        public $Authentication;

        /**
         * An array of curl options to set
         *
         * @var array
         */
        public $Options;

        public function __construct()
        {
            $this->Type = HttpRequestType::GET;
            $this->Body = null;
            $this->Headers = [
                'User-Agent: ncc/1.0'
            ];
            $this->Options = [];
        }

        /**
         * Returns an array representation of the object.
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'type' => $this->Type,
                'url' => $this->Url,
                'headers' => $this->Headers,
                'body' => $this->Body,
                'authentication' => $this->Authentication,
                'options' => $this->Options
            ];
        }

        /**
         * Constructs a new HttpRequest object from an array representation.
         *
         * @param array $data
         * @return static
         */
        public static function fromArray(array $data): self
        {
            $request = new self();
            $request->Type = $data['type'];
            $request->Url = $data['url'];
            $request->Headers = $data['headers'];
            $request->Body = $data['body'];
            $request->Authentication = $data['authentication'];
            $request->Options = $data['options'];
            return $request;
        }
    }