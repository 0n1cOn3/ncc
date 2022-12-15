<?php

    namespace ncc\Interfaces;

    use ncc\Objects\DefinedRemoteSource;
    use ncc\Objects\RemotePackageInput;
    use ncc\Objects\Vault\Entry;

    interface RepositorySourceInterface
    {
        /**
         * Returns the git repository url of the repository, versions cannot be specified.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         */
        public static function fetchGitUri(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry=null): string;

        /**
         * Returns the release url of the repository, versions can be specified.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         */
        public static function getRelease(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string;

        /**
         * Returns the download URL of the pre-compiled .ncc package if available
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         */
        public static function getNccPackage(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string;
    }