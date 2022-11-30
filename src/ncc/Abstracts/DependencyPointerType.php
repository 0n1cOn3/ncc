<?php

    namespace ncc\Abstracts;

    abstract class DependencyPointerType
    {
        /**
         * Indicates if the dependency is statically linked and the
         * reference points to the file name of the dependency
         */
        const RelativeFile = 'relative_file';

        /**
         * Indicates if the pointer reference points to a remote source
         * to fetch the dependency from
         */
        const RemoteSource = 'remote_source';
    }