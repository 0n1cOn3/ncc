<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Utilities\Functions;

    class PackageEntry
    {
        /**
         * The name of the package that's installed
         *
         * @var string
         */
        public $Name;

        /**
         * An array of installed versions for this package
         *
         * @var VersionEntry[]
         */
        public $Versions;

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
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            $versions = [];
            foreach($this->Versions as $dependency)
            {
                $versions[] = $dependency->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('name')  : 'name')  => $this->Name,
                ($bytecode ? Functions::cbc('versions')  : 'versions')  => $versions,
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
            $versions = Functions::array_bc($data, 'versions');

            if($versions !== null)
            {
                foreach($versions as $_datum)
                {
                    $object->Versions[] = DependencyEntry::fromArray($_datum);
                }
            }

            return $object;
        }
    }