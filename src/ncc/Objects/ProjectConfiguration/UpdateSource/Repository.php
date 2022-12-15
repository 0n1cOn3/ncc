<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ProjectConfiguration\UpdateSource;

    use ncc\Utilities\Functions;

    class Repository
    {
        /**
         * The name of the remote source to add
         *
         * @var string
         */
        public $Name;

        /**
         * The type of client that is used to connect to the remote source
         *
         * @var string|null
         */
        public $Type;

        /**
         * The host of the remote source
         *
         * @var string
         */
        public $Host;

        /**
         * If SSL should be used
         *
         * @var bool
         */
        public $SSL;

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('name') : 'name') => $this->Name,
                ($bytecode ? Functions::cbc('type') : 'type') => $this->Type,
                ($bytecode ? Functions::cbc('host') : 'host') => $this->Host,
                ($bytecode ? Functions::cbc('ssl') : 'ssl') => $this->SSL
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Repository
         */
        public static function fromArray(array $data): self
        {
            $obj = new self();
            $obj->Name = Functions::array_bc($data, 'name');
            $obj->Type = Functions::array_bc($data, 'type');
            $obj->Host = Functions::array_bc($data, 'host');
            $obj->SSL = Functions::array_bc($data, 'ssl');
            return $obj;
        }
    }