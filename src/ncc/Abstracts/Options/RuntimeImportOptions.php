<?php

    namespace ncc\Abstracts\Options;

    abstract class RuntimeImportOptions
    {
        /**
         * Indicates if the import should require PHP's autoload.php file
         * for the package (Only applies to PHP packages)
         */
        const ImportAutoloader = 'import_autoloader';

        /**
         * Indicates if the import should require all static files
         * for the package (Only applies to PHP packages)
         */
        const ImportStaticFiles = 'import_static_files';
    }