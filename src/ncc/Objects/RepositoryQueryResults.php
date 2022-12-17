<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Objects\RepositoryQueryResults\Files;

    class RepositoryQueryResults
    {
        /**
         * A collection of files that are available for download
         *
         * @var Files
         */
        public $Files;

        /**
         * The version of the package returned by the query
         *
         * @var string|null
         */
        public $Version;

        /**
         * The name of the release returned by the query
         *
         * @var string|null
         */
        public $ReleaseName;

        /**
         * The description of the release returned by the query
         *
         * @var string|null
         */
        public $ReleaseDescription;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Files = new Files();
        }
    }