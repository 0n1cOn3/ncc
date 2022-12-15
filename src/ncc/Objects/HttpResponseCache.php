<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Utilities\RuntimeCache;

    class HttpResponseCache
    {
        /**
         * The cache of response
         *
         * @var HttpResponse
         */
        private $httpResponse;

        /**
         * The Unix Timestamp of when the cache becomes invalid
         *
         * @var int
         */
        private $ttl;

        /**
         * Creates a new HttpResponseCache
         *
         * @param HttpResponse $httpResponse
         * @param int $ttl
         */
        public function __construct(HttpResponse $httpResponse, int $ttl)
        {
            $this->httpResponse = $httpResponse;
            $this->ttl = $ttl;
        }

        /**
         * Returns the cached response
         *
         * @return HttpResponse
         */
        public function getHttpResponse(): HttpResponse
        {
            return $this->httpResponse;
        }

        /**
         * Returns the Unix Timestamp of when the cache becomes invalid
         *
         * @return int
         */
        public function getTtl(): int
        {
            return $this->ttl;
        }
    }