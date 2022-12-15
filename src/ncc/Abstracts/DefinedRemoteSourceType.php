<?php

    namespace ncc\Abstracts;

    abstract class DefinedRemoteSourceType
    {
        /**
         * The remote source is from a generic remote git server
         * (Will search for packages with /group/package)
         *
         * For example if the host is git.example.com and the package is
         * group/package, the package will be fetched from
         * https://git.example.com/group/package.git
         *
         * The git client will be used to fetch the package
         * but NCC will not be able to easily check for updates
         * without having to pull the entire repository
         */
        const Git = 'git';

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
            self::Git,
            self::Gitlab,
            self::Github
        ];
    }