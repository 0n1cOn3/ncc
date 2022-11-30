<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\Package;

    use ncc\Abstracts\DependencyPointerType;

    class DependencyPointer
    {
        /**
         * The name of the package
         *
         * @var string
         */
        public $Name;

        /**
         * The version of the package
         *
         * @var string
         */
        public $Version;

        /**
         * The type of the dependency pointer
         *
         * @var string|DependencyPointerType
         */
        public $Type;

        /**
         * The refrence to how to fetch the dependency
         *
         * @var string
         */
        public $Reference;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'name' => $this->Name,
                'version' => $this->Version,
                'type' => $this->Type,
                'reference' => $this->Reference
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
            $pointer = new self();
            $pointer->Name = $data['name'];
            $pointer->Version = $data['version'];
            $pointer->Type = $data['type'];
            $pointer->Reference = $data['reference'];
            return $pointer;
        }
    }