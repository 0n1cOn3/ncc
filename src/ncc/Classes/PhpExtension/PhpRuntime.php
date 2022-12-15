<?php

    namespace ncc\Classes\PhpExtension;

    use ncc\Abstracts\RuntimeImportOptions;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Interfaces\RuntimeInterface;
    use ncc\Objects\PackageLock\VersionEntry;
    use ncc\Utilities\IO;
    use ncc\ZiProto\ZiProto;

    class PhpRuntime implements RuntimeInterface
    {

        /**
         * Attempts to import a PHP package
         *
         * @param VersionEntry $versionEntry
         * @param array $options
         * @return bool
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         */
        public static function import(VersionEntry $versionEntry, array $options=[]): bool
        {
            $autoload_path = $versionEntry->getInstallPaths()->getBinPath() . DIRECTORY_SEPARATOR . 'autoload.php';
            $static_files = $versionEntry->getInstallPaths()->getBinPath() . DIRECTORY_SEPARATOR . 'static_autoload.bin';

            if(file_exists($autoload_path) && !in_array(RuntimeImportOptions::ImportAutoloader, $options))
            {
                require_once($autoload_path);
            }

            if(file_exists($static_files) && !in_array(RuntimeImportOptions::ImportStaticFiles, $options))
            {
                $static_files = ZiProto::decode(IO::fread($static_files));
                foreach($static_files as $file)
                    require_once($file);
            }

            if(!file_exists($autoload_path) && !file_exists($static_files))
                return false;

            return true;
        }
    }