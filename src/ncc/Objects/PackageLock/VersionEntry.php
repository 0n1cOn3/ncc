<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\Compiler;
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
         * The compiler extension used for the package
         *
         * @var Compiler
         */
        public $Compiler;

        /**
         * An array of packages that this package depends on
         *
         * @var DependencyEntry[]
         */
        public $Dependencies;

        /**
         * @var ExecutionUnit[]
         */
        public $ExecutionUnits;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Dependencies = [];
            $this->ExecutionUnits = [];
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

            $execution_units = [];
            foreach($this->ExecutionUnits as $executionUnit)
            {
                $execution_units[] = $executionUnit->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('version')  : 'version')  => $this->Version,
                ($bytecode ? Functions::cbc('compiler')  : 'compiler')  => $this->Compiler->toArray($bytecode),
                ($bytecode ? Functions::cbc('dependencies')  : 'dependencies')  => $dependencies,
                ($bytecode ? Functions::cbc('execution_units')  : 'execution_units')  => $execution_units,
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
            $object->Compiler = Compiler::fromArray(Functions::array_bc($data, 'compiler'));

            $dependencies = Functions::array_bc($data, 'dependencies');
            if($dependencies !== null)
            {
                foreach($dependencies as $_datum)
                {
                    $object->Dependencies[] = DependencyEntry::fromArray($_datum);
                }
            }

            $execution_units = Functions::array_bc($data, 'execution_units');
            if($execution_units !== null)
            {
                foreach($execution_units as $_datum)
                {
                    $object->ExecutionUnits[] = ExecutionUnit::fromArray($_datum);
                }
            }

            return $object;
        }
    }