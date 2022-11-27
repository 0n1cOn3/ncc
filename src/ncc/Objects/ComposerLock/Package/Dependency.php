<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ComposerLock\Package;

    class Dependency
    {
        /**
         * @var string|null
         */
        public $Package;

        /**
         * @var string|null
         */
        public $Version;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'package' => $this->Package,
                'version' => $this->Version
            ];
        }

        /**
         * @param array $data
         * @return Dependency
         */
        public static function fromArray(array $data): self
        {
            $dependency = new self();
            $dependency->Package = $data['package'] ?? null;
            $dependency->Version = $data['version'] ?? null;
            return $dependency;
        }
    }