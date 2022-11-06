<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;
    use ncc\Utilities\Functions;

    class VersionEntry
    {
        /**
         * The version of the package that's installed
         *
         * @var string
         */
        public $Version;

        /**
         * An array of packages that this package depends on
         *
         * @var DependencyEntry[]
         */
        public $Dependencies;

        /**
         * The main execution policy for this package version
         *
         * @var ExecutionPolicy
         */
        public $MainExecutionPolicy;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Dependencies = [];
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            $dependencies = [];
            foreach($this->Dependencies as $dependency)
            {
                $dependencies[] = $dependency->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('version')  : 'version')  => $this->Version,
                ($bytecode ? Functions::cbc('dependencies')  : 'dependencies')  => $dependencies,
                ($bytecode ? Functions::cbc('main_execution_policy')  : 'main_execution_policy')  => $this->MainExecutionPolicy->toArray($bytecode),
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return VersionEntry
         */
        public static function fromArray(array $data): self
        {
            $object = new self();
            $object->Version = Functions::array_bc($data, 'version');
            $object->MainExecutionPolicy = Functions::array_bc($data, 'main_execution_policy');

            $dependencies = Functions::array_bc($data, 'dependencies');
            if($dependencies !== null)
            {
                foreach($dependencies as $_datum)
                {
                    $object->Dependencies[] = DependencyEntry::fromArray($_datum);
                }
            }

            return $object;
        }
    }