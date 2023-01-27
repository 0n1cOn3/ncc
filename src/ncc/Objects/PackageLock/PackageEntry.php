<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Abstracts\Scopes;
    use ncc\Abstracts\Versions;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Objects\Package;
    use ncc\Objects\ProjectConfiguration\UpdateSource;
    use ncc\ThirdParty\jelix\Version\VersionComparator;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;
    use ncc\Utilities\Functions;
    use ncc\Utilities\PathFinder;
    use ncc\Utilities\Resolver;

    class PackageEntry
    {
        /**
         * The name of the package that's installed
         *
         * @var string
         */
        public $Name;

        /**
         * The latest version of the package entry, this is updated automatically
         *
         * @var string|null
         */
        private $LatestVersion;

        /**
         * An array of installed versions for this package
         *
         * @var VersionEntry[]
         */
        public $Versions;

        /**
         * The update source of the package entry
         *
         * @var UpdateSource|null
         */
        public $UpdateSource;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Versions = [];
        }

        /**
         * Searches and returns a version of the package
         *
         * @param string $version
         * @param bool $throw_exception
         * @return VersionEntry|null
         * @throws VersionNotFoundException
         */
        public function getVersion(string $version, bool $throw_exception=false): ?VersionEntry
        {
            if($version == Versions::Latest)
                $version = $this->LatestVersion;

            foreach($this->Versions as $versionEntry)
            {
                if($versionEntry->Version == $version)
                {
                    return $versionEntry;
                }
            }

            if($throw_exception)
                throw new VersionNotFoundException('The version entry is not found');

            return null;
        }

        /**
         * Removes version entry from the package
         *
         * @param string $version
         * @return bool
         * @noinspection PhpUnused
         */
        public function removeVersion(string $version): bool
        {
            $count = 0;
            $found_node = false;
            foreach($this->Versions as $versionEntry)
            {
                if($versionEntry->Version == $version)
                {
                    $found_node = true;
                    break;
                }

                $count += 1;
            }

            if($found_node)
            {
                unset($this->Versions[$count]);
                $this->updateLatestVersion();
                return true;
            }

            return false;
        }

        /**
         * Adds a new version entry to the package, if overwrite is true then
         * the entry will be overwritten if it exists, otherwise it will return
         * false.
         *
         * @param Package $package
         * @param string $install_path
         * @param bool $overwrite
         * @return bool
         */
        public function addVersion(Package $package, string $install_path, bool $overwrite=false): bool
        {
            try
            {
                if ($this->getVersion($package->Assembly->Version) !== null)
                {
                    if (!$overwrite) return false;
                    $this->removeVersion($package->Assembly->Version);
                }
            }
            catch (VersionNotFoundException $e)
            {
                unset($e);
            }

            $version = new VersionEntry();
            $version->Version = $package->Assembly->Version;
            $version->Compiler = $package->Header->CompilerExtension;
            $version->ExecutionUnits = $package->ExecutionUnits;
            $version->MainExecutionPolicy = $package->MainExecutionPolicy;
            $version->Location = $install_path;

            foreach($version->ExecutionUnits as $unit)
                $unit->Data = null;

            foreach($package->Dependencies as $dependency)
            {
                $version->Dependencies[] = new DependencyEntry($dependency);
            }

            $this->Versions[] = $version;
            $this->updateLatestVersion();
            return true;
        }

        /**
         * Updates and returns the latest version of this package entry
         *
         * @return void
         */
        private function updateLatestVersion(): void
        {
            $latest_version = null;
            foreach($this->Versions as $version)
            {
                $version = $version->Version;
                if($latest_version == null)
                {
                    $latest_version = $version;
                    continue;
                }
                if(VersionComparator::compareVersion($version, $latest_version))
                    $latest_version = $version;
            }

            $this->LatestVersion = $latest_version;
        }

        /**
         * @return string|null
         */
        public function getLatestVersion(): ?string
        {
            return $this->LatestVersion;
        }

        /**
         * Returns an array of all versions installed
         *
         * @return array
         */
        public function getVersions(): array
        {
            $r = [];

            foreach($this->Versions as $version)
            {
                $r[] = $version->Version;
            }

            return $r;
        }

        /**
         * @return string
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         */
        public function getDataPath(): string
        {
            $path = PathFinder::getPackageDataPath($this->Name);

            if(!file_exists($path) && Resolver::resolveScope() == Scopes::System)
            {
                $filesystem = new Filesystem();
                $filesystem->mkdir($path, 0777);
            }

            return $path;
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            $versions = [];
            foreach($this->Versions as $version)
            {
                $versions[] = $version->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('name')  : 'name')  => $this->Name,
                ($bytecode ? Functions::cbc('latest_version')  : 'latest_version')  => $this->LatestVersion,
                ($bytecode ? Functions::cbc('versions')  : 'versions')  => $versions,
                ($bytecode ? Functions::cbc('update_source')  : 'update_source')  => ($this->UpdateSource?->toArray($bytecode) ?? null),
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return PackageEntry
         */
        public static function fromArray(array $data): self
        {
            $object = new self();

            $object->Name = Functions::array_bc($data, 'name');
            $object->LatestVersion = Functions::array_bc($data, 'latest_version');
            $versions = Functions::array_bc($data, 'versions');
            $object->UpdateSource = Functions::array_bc($data, 'update_source');

            if($object->UpdateSource !== null)
                $object->UpdateSource = UpdateSource::fromArray($object->UpdateSource);

            if($versions !== null)
            {
                foreach($versions as $_datum)
                {
                    $object->Versions[] = VersionEntry::fromArray($_datum);
                }
            }

            return $object;
        }

    }