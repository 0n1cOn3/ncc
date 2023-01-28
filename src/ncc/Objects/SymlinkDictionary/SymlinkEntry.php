<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\SymlinkDictionary;

    use ncc\Utilities\Functions;

    class SymlinkEntry
    {
        /**
         * The name of the package that the symlink is for
         *
         * @var string
         */
        public $Package;

        /**
         * The name of the execution policy to execute
         *
         * @var string
         */
        public $ExecutionPolicyName;

        /**
         * Indicates if this symlink is currently registered by NCC
         *
         * @var bool
         */
        public $Registered;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->ExecutionPolicyName = 'main';
            $this->Registered = false;
        }

        /**
         * Returns a string representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('package') : 'package') => $this->Package,
                ($bytecode ? Functions::cbc('registered') : 'registered') => $this->Registered,
                ($bytecode ? Functions::cbc('execution_policy_name') : 'execution_policy_name') => $this->ExecutionPolicyName
            ];
        }

        /**
         * Constructs a new SymlinkEntry from an array representation
         *
         * @param array $data
         * @return SymlinkEntry
         */
        public static function fromArray(array $data): SymlinkEntry
        {
            $entry = new SymlinkEntry();

            $entry->Package = Functions::array_bc($data, 'package');
            $entry->Registered = (bool)Functions::array_bc($data, 'registered');
            $entry->ExecutionPolicyName = Functions::array_bc($data, 'execution_policy_name');

            return $entry;
        }

    }