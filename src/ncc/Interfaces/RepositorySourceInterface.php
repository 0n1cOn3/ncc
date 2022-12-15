<?php

    namespace ncc\Interfaces;

    use ncc\Objects\DefinedRemoteSource;
    use ncc\Objects\RemotePackageInput;

    interface RepositorySourceInterface
    {
        /**
         * Fetches a package and all it's dependencies from the given remote source
         * and optionally converts and compiles it to a local package, returns the
         * fetched package as a path to the ncc package file. This function uses
         * a defined remote source to fetch the package or build the package from.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @return string
         */
        public static function fetch(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource): string;
    }