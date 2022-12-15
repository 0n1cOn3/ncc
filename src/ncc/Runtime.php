<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc;

    use Exception;
    use ncc\Abstracts\CompilerExtensions;
    use ncc\Abstracts\Versions;
    use ncc\Classes\PhpExtension\PhpRuntime;
    use ncc\Exceptions\ImportException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Managers\PackageManager;
    use ncc\Objects\PackageLock\VersionEntry;

    class Runtime
    {
        /**
         * @var PackageManager
         */
        private static $package_manager;

        /**
         * @var array
         */
        private static $imported_packages;

        /**
         * Determines if the package is already imported
         *
         * @param string $package
         * @param string $version
         * @return bool
         * @throws PackageLockException
         */
        private static function isImported(string $package, string $version=Versions::Latest): bool
        {
            if($version == Versions::Latest)
                $version = self::getPackageManager()->getPackage($package)->getLatestVersion();

            $entry = "$package=$version";

            return isset(self::$imported_packages[$entry]);
        }

        /**
         * Adds a package to the imported packages list
         *
         * @param string $package
         * @param string $version
         * @return void
         */
        private static function addImport(string $package, string $version): void
        {
            $entry = "$package=$version";
            self::$imported_packages[$entry] = true;
        }


        /**
         * @param string $package
         * @param string $version
         * @param array $options
         * @return void
         * @throws ImportException
         */
        public static function import(string $package, string $version=Versions::Latest, array $options=[]): void
        {
            try
            {
                $package_entry = self::getPackageManager()->getPackage($package);
            }
            catch (PackageLockException $e)
            {
                throw new ImportException(sprintf('Failed to import package "%s" due to a package lock exception: %s', $package, $e->getMessage()), $e);
            }
            if($package_entry == null)
            {
                throw new ImportException(sprintf("Package '%s' not found", $package));
            }

            if($version == Versions::Latest)
                $version = $package_entry->getLatestVersion();

            try
            {
                /** @var VersionEntry $version_entry */
                $version_entry = $package_entry->getVersion($version);

                if($version_entry == null)
                    throw new VersionNotFoundException();
            }
            catch (VersionNotFoundException $e)
            {
                throw new ImportException(sprintf('Version %s of %s is not installed', $version, $package), $e);
            }


            try
            {
                if (self::isImported($package, $version))
                    return;
            }
            catch (PackageLockException $e)
            {
                throw new ImportException(sprintf('Failed to check if package %s is imported', $package), $e);
            }

            if($version_entry->Dependencies !== null && count($version_entry->Dependencies) > 0)
            {
                // Import all dependencies first
                foreach($version_entry->Dependencies as $dependency)
                    self::import($dependency->Package, $dependency->Version, $options);
            }

            try
            {
                switch($version_entry->Compiler->Extension)
                {
                    case CompilerExtensions::PHP:
                        PhpRuntime::import($version_entry, $options);
                        break;

                    default:
                        throw new ImportException(sprintf('Compiler extension %s is not supported in this runtime', $version_entry->Compiler->Extension));
                }
            }
            catch(Exception $e)
            {
                throw new ImportException(sprintf('Failed to import package %s', $package), $e);
            }


            self::addImport($package, $version);
        }

        /**
         * @return PackageManager
         */
        private static function getPackageManager(): PackageManager
        {
            if(self::$package_manager == null)
                self::$package_manager = new PackageManager();
            return self::$package_manager;
        }

        /**
         * Returns an array of all the packages that is currently imported
         *
         * @return array
         */
        public static function getImportedPackages(): array
        {
            return array_keys(self::$imported_packages);
        }
    }