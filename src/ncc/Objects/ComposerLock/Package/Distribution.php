<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ComposerLock\Package;

    class Distribution
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
         * @var string|null
         */
        public $Shasum;

        /**
         * Constructs object from an array representation
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'type' => $this->Type,
                'url' => $this->URL,
                'reference' => $this->Reference,
                'shasum' => $this->Shasum
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
            $distribution = new self();
            $distribution->Type = $data['type'] ?? null;
            $distribution->URL = $data['url'] ?? null;
            $distribution->Reference = $data['reference'] ?? null;
            $distribution->Shasum = $data['shasum'] ?? null;
            return $distribution;
        }
    }