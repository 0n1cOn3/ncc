<?php

    namespace ncc\Interfaces;

    use ncc\Abstracts\AuthenticationType;

    interface PasswordInterface
    {
        /**
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array;

        /**
         * @param array $data
         * @return static
         */
        public static function fromArray(array $data): self;

        /**
         * @return string
         */
        public function getAuthenticationType(): string;

        /**
         * @return string
         */
        public function __toString(): string;
    }