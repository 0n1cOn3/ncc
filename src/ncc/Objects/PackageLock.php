<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Abstracts\Versions;
    use ncc\Objects\PackageLock\PackageEntry;
    use ncc\Utilities\Functions;

    class PackageLock
    {
        /**
         * The version of package lock file structure
         *
         * @var string
         */
        public $PackageLockVersion;

        /**
         * The Unix Timestamp for when this package lock file was last updated
         *
         * @var int
         */
        public $LastUpdatedTimestamp;

        /**
         * An array of installed packages in the PackageLock file
         *
         * @var PackageEntry[]
         */
        public $Packages;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->PackageLockVersion = Versions::PackageLockVersion;
            $this->Packages = [];
        }

        /**
         * Updates the version and timestamp
         *
         * @return void
         */
        private function update(): void
        {
            $this->PackageLockVersion = Versions::PackageLockVersion;
            $this->LastUpdatedTimestamp = time();
        }

        /**
         * @param Package $package
         * @return void
         */
        public function addPackage(Package $package): void
        {
            if(!isset($this->Packages[$package->Assembly->Package]))
            {
                $package_entry = new PackageEntry();
                $package_entry->addVersion($package, true);
                $this->Packages[$package->Assembly->Package] = $package_entry;
                $this->update();
            }
        }

        /**
         * Removes a package version entry, removes the entire entry if there are no installed versions
         *
         * @param string $package
         * @param string $version
         * @return bool
         */
        public function removePackageVersion(string $package, string $version): bool
        {
            if(isset($this->Packages[$package]))
            {
                $r = $this->Packages[$package]->removeVersion($version);

                // Remove the entire package entry if there's no installed versions
                if($this->Packages[$package]->getLatestVersion() == null && $r)
                {
                    unset($this->Packages[$package]);
                }

                $this->update();
                return $r;
            }

            return false;
        }

        /**
         * Removes an entire package entry
         *
         * @param string $package
         * @return bool
         */
        public function removePackage(string $package): bool
        {
            if(isset($this->Packages[$package]))
            {
                unset($this->Packages[$package]);
                return true;
            }

            return false;
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode): array
        {
            $packages = [];
            foreach($this->Packages as $package)
            {
                $packages[] = $package->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('package_lock_version')  : 'package_lock_version') => $this->PackageLockVersion,
                ($bytecode ? Functions::cbc('last_updated_timestamp') : 'last_updated_timestamp') => $this->LastUpdatedTimestamp,
                ($bytecode ? Functions::cbc('packages') : 'packages') => $packages
             ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return static
         */
        public static function fromArray(array $data): self
        {
            $object = new self();

            $packages = Functions::array_bc($data, 'packages');
            if($packages !== null)
            {
                foreach($packages as $_datum)
                {
                    $object->Packages[] = Package::fromArray($_datum);
                }
            }

            $object->PackageLockVersion = Functions::array_bc($data, 'package_lock_version');
            $object->LastUpdatedTimestamp = Functions::array_bc($data, 'last_updated_timestamp');

            return $object;
        }
    }