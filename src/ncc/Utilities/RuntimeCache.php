<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Utilities;

    use Exception;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;

    class RuntimeCache
    {
        /**
         * An array of cache entries
         *
         * @var array
         */
        private static $cache = [];

        /**
         * An array of files to delete when the cache is cleared
         *
         * @var string[]
         */
        private static $temporary_files = [];

        /**
         * Sets a value, returns the value
         *
         * @param $key
         * @param $value
         * @return mixed
         */
        public static function set($key, $value): mixed
        {
            Console::outDebug(sprintf('setting cache entry \'%s\'', $key));
            self::$cache[$key] = $value;
            return $value;
        }

        /**
         * Gets an existing value, null if it doesn't exist
         *
         * @param $key
         * @return mixed|null
         */
        public static function get($key): mixed
        {
            Console::outDebug(sprintf('getting cache entry \'%s\'', $key));
            if(isset(self::$cache[$key]))
                return self::$cache[$key];

            return null;
        }

        /**
         * Sets a file as temporary, it will be deleted when the cache is cleared
         *
         * @param string $path
         * @return void
         */
        public static function setFileAsTemporary(string $path): void
        {
            Console::outDebug(sprintf('setting file \'%s\' as temporary', $path));
            if(!in_array($path, self::$temporary_files))
                self::$temporary_files[] = $path;
        }

        /**
         * Removes a file from the temporary files list
         *
         * @param string $path
         * @return void
         * @noinspection PhpUnused
         */
        public static function removeFileAsTemporary(string $path): void
        {
            Console::outDebug(sprintf('removing file \'%s\' from temporary files list', $path));
            if(in_array($path, self::$temporary_files))
                unset(self::$temporary_files[array_search($path, self::$temporary_files)]);
        }

        /**
         * @param bool $clear_memory
         * @param bool $clear_files
         * @return void
         */
        public static function clearCache(bool $clear_memory=true, bool $clear_files=true): void
        {
            if($clear_memory)
            {
                Console::outDebug('clearing memory cache');
                self::$cache = [];
            }

            if($clear_files)
            {
                Console::outDebug('clearing temporary files');
                $filesystem = new Filesystem();
                foreach(self::$temporary_files as $file)
                {
                    try
                    {
                        $filesystem->remove($file);
                        Console::outDebug(sprintf('deleted temporary file \'%s\'', $file));
                    }
                    catch (Exception $e)
                    {
                        Console::outDebug(sprintf('failed to delete temporary file \'%s\', %s', $file, $e->getMessage()));
                        unset($e);
                    }
                }
            }
        }
    }