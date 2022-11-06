<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Objects\Package\MainExecutionPolicy;
    use ncc\Objects\ProjectConfiguration\Compiler;
    use ncc\Utilities\Functions;

    class PackageEntry
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
         * An array of installed versions for this package
         *
         * @var PackageEntry[]
         */
        public $Versions;

        /**
         * @var MainExecutionPolicy
         */
        public $MainExecutionPolicy;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Versions = [];
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
                ($bytecode ? Functions::cbc('compiler')  : 'compiler')  => $this->Compiler->toArray($bytecode),
                ($bytecode ? Functions::cbc('name')  : 'name')  => $this->Name,
                ($bytecode ? Functions::cbc('versions')  : 'versions')  => $versions,
                ($bytecode ? Functions::cbc('main_execution_policy')  : 'main_execution_policy')  => $this->MainExecutionPolicy?->toArray($bytecode),
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

            $object->Compiler = Compiler::fromArray(Functions::array_bc($data, 'compiler'));
            $object->Name = Functions::array_bc($data, 'name');
            $object->MainExecutionPolicy = Functions::array_bc($data, 'main_execution_policy');

            $versions = Functions::array_bc($data, 'versions');
            if($versions !== null)
            {
                foreach($versions as $_datum)
                {
                    $object->Versions[] = DependencyEntry::fromArray($_datum);
                }
            }

            if($object->MainExecutionPolicy !== null)
                $object->MainExecutionPolicy = MainExecutionPolicy::fromArray($object->MainExecutionPolicy);

            return $object;
        }
    }