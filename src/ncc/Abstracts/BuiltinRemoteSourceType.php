<?php

    namespace ncc\Abstracts;

    abstract class BuiltinRemoteSourceType
    {
        /**
         * The remote source indicates the package is to be
         * fetched using the composer utility.
         */
        const Composer = 'composer';


        const All = [
            self::Composer
        ];
    }