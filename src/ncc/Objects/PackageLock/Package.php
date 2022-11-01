<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Objects\ProjectConfiguration\Compiler;
    use ncc\Utilities\Functions;

    class Package
    {
        /**
         * The compiler extension used for the package
         *
         * @var Compiler
         */
        public $Compiler;

        /**
         * The name of the package that's installed
         *
         * @var string
         */
        public $Name;

        /**
         * The version of the package that's installed
         *
         * @var string
         */
        public $Version;

        /**
         * An array of packages that this package depends on
         *
         * @var Dependency[]
         */
        public $Dependencies;

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
                ($bytecode ? Functions::cbc('compiler')  : 'compiler')  => $this->Compiler->toArray($bytecode),
                ($bytecode ? Functions::cbc('name')  : 'name')  => $this->Name,
                ($bytecode ? Functions::cbc('version')  : 'version')  => $this->Version,
                ($bytecode ? Functions::cbc('dependencies')  : 'dependencies')  => $dependencies,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Package
         */
        public static function fromArray(array $data): self
        {
            $object = new self();

            $object->Compiler = Compiler::fromArray(Functions::array_bc($data, 'compiler'));
            $object->Name = Functions::array_bc($data, 'name');
            $object->Version = Functions::array_bc($data, 'version');

            $dependencies = Functions::array_bc($data, 'dependencies');
            if($dependencies !== null)
            {
                foreach($dependencies as $_datum)
                {
                    $object->Dependencies[] = Dependency::fromArray($_datum);
                }
            }

            return $object;
        }
    }