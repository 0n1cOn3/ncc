<?php
/*
 * Copyright (c) Nosial 2022-2023, all rights reserved.
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 *  associated documentation files (the "Software"), to deal in the Software without restriction, including without
 *  limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 *  Software, and to permit persons to whom the Software is furnished to do so, subject to the following
 *  conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all copies or substantial portions
 *  of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *  INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *  PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 *  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 *  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 *
 */

namespace ncc;
    
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\MalformedJsonException;
    use ncc\Exceptions\RuntimeException;
    use ncc\Objects\NccVersionInformation;
    use ncc\Utilities\Functions;

    /**
     * @author Zi Xing Narrakas
     * @copyright Copyright (C) 2022-2022. Nosial - All Rights Reserved.
     */
    class ncc
    {
    
        /**
         * The cache'd version of the version information object.
         *
         * @var NccVersionInformation|null
         */
        private static $VersionInformation;

        /**
         * NCC Public Constructor
         */
        public function __construct()
        {
            
        }

        /**
         * Returns the version information object about the current build of NCC
         *
         * @param boolean $reload Indicates if the cached version is to be ignored and the version file to be reloaded and validated
         * @return NccVersionInformation
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws RuntimeException
         */
        public static function getVersionInformation(bool $reload=False): NccVersionInformation
        {
            if(self::$VersionInformation !== null && !$reload)
                return self::$VersionInformation;

            if(!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'version.json'))
            {
                throw new RuntimeException('The file \'version.json\' was not found in \'' . __DIR__ . '\'');
            }

            try
            {
                self::$VersionInformation = NccVersionInformation::fromArray(Functions::loadJsonFile(__DIR__ . DIRECTORY_SEPARATOR . 'version.json', Functions::FORCE_ARRAY));
            }
            catch(MalformedJsonException $e)
            {
                throw new RuntimeException('Unable to parse JSON contents of \'version.json\' in \'' . __DIR__ . '\'', $e);
            }

            if(self::$VersionInformation->Version == null)
            {
                throw new RuntimeException('The version number is not specified in the version information file');
            }

            if(self::$VersionInformation->Branch == null)
            {
                throw new RuntimeException('The version branch is not specified in the version information file');
            }

            return self::$VersionInformation;
        }

        /**
         * Initializes the NCC environment
         *
         * @return bool
         * @throws Exceptions\FileNotFoundException
         * @throws RuntimeException
         */
        public static function initialize(): bool
        {
            if(defined('NCC_INIT'))
                return false;
            
            // Set debugging/troubleshooting constants
            define('NCC_EXEC_LOCATION', __DIR__); // The directory of where ncc.php is located
            define('NCC_EXEC_IWD', getcwd()); // The initial working directory when NCC was first invoked

            // Set version information about the current build
            $VersionInformation = self::getVersionInformation(true);
            define('NCC_VERSION_NUMBER', $VersionInformation->Version);
            define('NCC_VERSION_BRANCH', $VersionInformation->Branch);
            define('NCC_VERSION_UPDATE_SOURCE', $VersionInformation->UpdateSource);
            define('NCC_VERSION_FLAGS', $VersionInformation->Flags);

            define('NCC_INIT', 1);
            return true;
        }

        /**
         * Determines if NCC is currently in CLI mode or not
         *
         * @return bool
         */
        public static function cliMode(): bool
        {
            // TODO: Optimize this function to reduce redundant calls

            if(defined('NCC_CLI_MODE') && NCC_CLI_MODE == 1)
            {
                return true;
            }

            return false;
        }

        /**
         * Returns the constants set by NCC
         *
         * @return array
         * @throws RuntimeException
         */
        public static function getConstants(): array
        {
            if(defined('NCC_INIT') == false)
            {
                throw new RuntimeException('NCC Must be initialized before executing ' . get_called_class() . '::getConstants()');
            }

            return [
                // Init
                'NCC_INIT' => constant('NCC_INIT'),

                // Debugging/Troubleshooting constants
                'NCC_EXEC_LOCATION' => constant('NCC_EXEC_LOCATION'),
                'NCC_EXEC_IWD' => constant('NCC_EXEC_IWD'),

                // Version Information
                'NCC_VERSION_NUMBER' => constant('NCC_VERSION_NUMBER'),
                'NCC_VERSION_BRANCH' => constant('NCC_VERSION_BRANCH'),
                'NCC_VERSION_UPDATE_SOURCE' => constant('NCC_VERSION_UPDATE_SOURCE'),
                'NCC_VERSION_FLAGS' => constant('NCC_VERSION_FLAGS'),

                // Runtime Information
                'NCC_CLI_MODE' => (defined('NCC_CLI_MODE') ? NCC_CLI_MODE : 0) // May not be set during runtime initialization
            ];
        }
    }
    
