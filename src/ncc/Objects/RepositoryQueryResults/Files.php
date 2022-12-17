<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\RepositoryQueryResults;

    class Files
    {
        /**
         * The URL that points to a pre-compiled .ncc package
         *
         * @var string|null
         */
        public $PackageUrl;

        /**
         * The URL that points to a archived version of the source code
         *
         * @var string|null
         */
        public $SourceUrl;

        /**
         * The URL that points to a tarball archive of the repository
         *
         * @var string|null
         */
        public $TarballUrl;

        /**
         * The URL that points to a zip archive of the repository
         *
         * @var string
         */
        public $ZipballUrl;

        /**
         * The URL that points to the repository's source code
         *
         * @var string
         */
        public $GitHttpUrl;

        /**
         * The URL that points to the repository's source code
         *
         * @var string
         */
        public $GitSshUrl;
    }