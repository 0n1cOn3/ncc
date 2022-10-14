<?php

    namespace ncc\Objects\Package;

    class MainExecutionPolicy
    {
        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return MainExecutionPolicy
         */
        public static function fromArray(array $data): self
        {
            $object = new self();

            return $object;
        }
    }