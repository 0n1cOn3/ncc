<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\PackageLock;

    use ncc\Utilities\Functions;

    class Dependency
    {
        /**
         * The name of the package dependency
         *
         * @var string
         */
        public $PackageName;

        /**
         * The version of the package dependency
         *
         * @var string
         */
        public $Version;

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('package_name') : 'package_name') => $this->PackageName,
                ($bytecode ? Functions::cbc('version') : 'version') => $this->Version,
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Dependency
         */
        public static function fromArray(array $data): self
        {
            $object = new self();

            $object->PackageName = Functions::array_bc($data, 'package_name');
            $object->Version = Functions::array_bc($data, 'version');

            return $object;
        }
    }