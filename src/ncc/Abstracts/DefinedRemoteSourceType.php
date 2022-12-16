<?php

    namespace ncc\Abstracts;

    abstract class DefinedRemoteSourceType
    {
        /**
         * THe remote source is from gitlab or a custom gitlab instance
         *
         * Will use an API wrapper to interact with the gitlab instance
         * to fetch the package and check for updates without having to
         * pull the entire repository
         *
         * Will still use git to fetch the package from the gitlab instance
         */
        const Gitlab = 'gitlab';

        /**
         * The remote source is from GitHub
         *
         * Will use an API wrapper to interact with the GitHub instance
         * to fetch the package and check for updates without having to
         * pull the entire repository
         *
         * Will still use git to fetch the package from the GitHub instance
         */
        const Github = 'github';

        const All = [
            self::Gitlab,
            self::Github
        ];
    }