<?php

    namespace ncc\Utilities;

    use ncc\Abstracts\Runners;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\RunnerExecutionException;
    use ncc\ThirdParty\Symfony\Process\ExecutableFinder;

    class PathFinder
    {
        /**
         * Returns the root path of the system
         *
         * @param bool $win32
         * @return string
         */
        public static function getRootPath(bool $win32=false): string
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && $win32)
                return "C:/"; // Emulation for unix only

            return realpath(DIRECTORY_SEPARATOR);
        }

        /**
         * Returns the path for where NCC is installed
         *
         * @return string
         */
        public static function getInstallationPath(): string
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            {
                return realpath(self::getRootPath() . DIRECTORY_SEPARATOR . 'ncc');
            }

            return realpath(self::getRootPath() . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'ncc');
        }

        /**
         * Returns the home directory of the user
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getHomePath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            $scope = Resolver::resolveScope($scope);

            if(!Validate::scope($scope, false))
            {
                throw new InvalidScopeException($scope);
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || $win32)
            {
                switch($scope)
                {
                    case Scopes::User:
                        return self::getRootPath($win32) . 'ncc' . DIRECTORY_SEPARATOR . 'user_home';
                    case Scopes::System:
                        return self::getRootPath($win32) . 'ncc' . DIRECTORY_SEPARATOR . 'system_home';
                }
            }

            switch($scope)
            {
                case Scopes::User:
                    $uid = posix_getuid();
                    return posix_getpwuid($uid)['dir'] . DIRECTORY_SEPARATOR . '.ncc';

                case Scopes::System:
                    return posix_getpwuid(0)['dir'] . DIRECTORY_SEPARATOR . '.ncc';
            }

            throw new InvalidScopeException($scope);
        }

        /**
         * Returns the path where all NCC installation data is stored
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getDataPath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            $scope = Resolver::resolveScope($scope);

            if(!Validate::scope($scope, false))
            {
                throw new InvalidScopeException($scope);
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || $win32)
            {
                switch($scope)
                {
                    case Scopes::User:
                        return self::getRootPath($win32) . 'ncc' . DIRECTORY_SEPARATOR . 'user';
                    case Scopes::System:
                        return self::getRootPath($win32) . 'ncc' . DIRECTORY_SEPARATOR . 'system';
                }
            }

            switch($scope)
            {
                case Scopes::User:
                    $uid = posix_getuid();
                    return posix_getpwuid($uid)['dir'] . DIRECTORY_SEPARATOR . '.ncc' . DIRECTORY_SEPARATOR . 'data';

                case Scopes::System:
                    return self::getRootPath() . 'var' . DIRECTORY_SEPARATOR . 'ncc';
            }

            throw new InvalidScopeException($scope);
        }

        /**
         * Returns the path where packages are installed
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getPackagesPath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'packages';
        }

        /**
         * Returns the path where cache files are stored
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getCachePath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'cache';
        }

        /**
         * Returns the path where Runner bin files are located and installed
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getRunnerPath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'runners';
        }

        /**
         * Returns the package lock file
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getPackageLock(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'package.lck';
        }

        /**
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getRemouteSources(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'sources';
        }

        /**
         * Returns an array of all the package lock files the current user can access (For global-cross referencing)
         *
         * @param bool $win32
         * @return array
         * @throws InvalidScopeException
         */
        public static function getPackageLockFiles(bool $win32=false): array
        {
            $results = [];
            $results[] = self::getPackageLock(Scopes::System, $win32);

            if(!in_array(self::getPackageLock(Scopes::User, $win32), $results))
            {
                $results[] = self::getPackageLock(Scopes::User, $win32);
            }

            return $results;
        }

        /**
         * Returns the path where package data is located
         *
         * @param string $package
         * @return string
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         */
        public static function getPackageDataPath(string $package): string
        {
            if(!Validate::packageName($package))
                throw new InvalidPackageNameException($package);

            return self::getDataPath(Scopes::System) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $package;
        }

        /**
         * Returns the file path where files for the given extension is stored
         *
         * @param string $scope
         * @param bool $win32
         * @return string
         * @throws InvalidScopeException
         */
        public static function getExtensionPath(string $scope=Scopes::Auto, bool $win32=false): string
        {
            return self::getDataPath($scope, $win32) . DIRECTORY_SEPARATOR . 'ext';
        }

        /**
         * Returns the configuration file path (ncc.yaml)
         *
         * @return string
         * @throws InvalidScopeException
         */
        public static function getConfigurationFile(): string
        {
            return self::getDataPath(Scopes::System) . DIRECTORY_SEPARATOR . 'ncc.yaml';
        }

        /**
         * Attempts to locate the executable path of the given program name
         *
         * @param string $runner
         * @return string
         * @throws RunnerExecutionException
         */
        public static function findRunner(string $runner): string
        {
            $executable_finder = new ExecutableFinder();

            $config_value = Functions::getConfigurationProperty(sprintf('runners.%s', $runner));
            if($config_value !== null)
            {
                if(file_exists($config_value) && is_executable($config_value))
                    return $config_value;

                Console::outWarning(sprintf('The configured \'%s\' executable path is invalid, trying to find it automatically...', $runner));
            }

            $exec_path = $executable_finder->find($runner);

            if($exec_path !== null)
                return $exec_path;

            throw new RunnerExecutionException(sprintf('Unable to find \'%s\' executable', $runner));
        }
    }