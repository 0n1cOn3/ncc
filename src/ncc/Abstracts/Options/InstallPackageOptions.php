<?php

    namespace ncc\Abstracts\Options;

    abstract class InstallPackageOptions
    {
        /**
         * Skips the installation of dependencies of the package
         *
         * @warning This will cause the package to fail to import of
         *          the dependencies are not met
         */
        const SkipDependencies = 'skip_dependencies';

        /**
         * Reinstall all packages if they are already installed
         * Including dependencies if they are being processed.
         */
        const Reinstall = 'reinstall';
    }