<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace ncc\Objects;

    use ncc\Abstracts\ProjectType;

    class ProjectDetectionResults
    {
        /**
         * The directory path that contains the project root (the directory that contains the project.json, etc.. file)
         *
         * @var string
         */
        public $ProjectPath;

        /**
         * The type of project that was detected
         *
         * @see ProjectType
         * @var string
         */
        public $ProjectType;
    }