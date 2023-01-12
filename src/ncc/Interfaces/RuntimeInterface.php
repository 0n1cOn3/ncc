<?php

    namespace ncc\Interfaces;

    use ncc\Exceptions\MissingDependencyException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Objects\PackageLock\VersionEntry;

    interface RuntimeInterface
    {
        /**
         * @param VersionEntry $versionEntry
         * @param array $options
         * @return mixed
         * @throws PackageNotFoundException
         * @throws VersionNotFoundException
         * @throws PackageLockException
         * @throws MissingDependencyException
         */
        public static function import(VersionEntry $versionEntry, array $options=[]): bool;
    }