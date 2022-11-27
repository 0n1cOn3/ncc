<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ComposerLock\Package;

    class Source
    {
        /**
         * @var string|null
         */
        public $Type;

        /**
         * @var string|null
         */
        public $URL;

        /**
         * @var string|null
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
                'type' => $this->Type,
                'url' => $this->URL,
                'reference' => $this->Reference
            ];
        }

        /**
         * Constructs from an array representaiton
         *
         * @param array $data
         * @return Source
         */
        public static function fromArray(array $data): Source
        {
            $object = new self();

            $object->Type = $data['type'] ?? null;
            $object->URL = $data['url'] ?? null;
            $object->Reference = $data['reference'] ?? null;

            return $object;
        }
    }