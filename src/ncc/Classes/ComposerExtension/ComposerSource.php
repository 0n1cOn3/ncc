<?php

    namespace ncc\Classes\ComposerExtension;

    class ComposerSource
    {
        public static function install(string $vendor, string $package, string $version)
        {
            ComposerInstance::require($vendor, $package, $version);

        }
    }