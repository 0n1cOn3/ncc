<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ProjectConfiguration;

    use ncc\Objects\ProjectConfiguration\UpdateSource\Repository;
    use ncc\Utilities\Functions;

    class UpdateSource
    {
        /**
         * The string format of where the source is located.
         *
         * @var string
         */
        public $Source;

        /**
         * The repository to use for the source
         *
         * @var Repository|null
         */
        public $Repository;

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('source') : 'source') => $this->Source,
                ($bytecode ? Functions::cbc('repository') : 'repository') => ($this->Repository?->toArray($bytecode))
            ];
        }


        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return UpdateSource
         */
        public static function fromArray(array $data): UpdateSource
        {
            $obj = new UpdateSource();
            $obj->Source = Functions::array_bc($data, 'source');
            $obj->Repository = Functions::array_bc($data, 'repository');

            if($obj->Repository !== null)
                $obj->Repository = Repository::fromArray($obj->Repository);

            return $obj;
        }
    }