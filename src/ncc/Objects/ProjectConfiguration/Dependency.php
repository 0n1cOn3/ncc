<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\ProjectConfiguration;

    use ncc\Abstracts\DependencySourceType;
    use ncc\Exceptions\InvalidDependencyConfiguration;
    use ncc\Utilities\Functions;
    use ncc\Utilities\Validate;

    /**
     * @author Zi Xing Narrakas
     * @copyright Copyright (C) 2022-2022. Nosial - All Rights Reserved.
     */
    class Dependency
    {
        /**
         * The name of the dependency
         *
         * @var string
         */
        public $Name;

        /**
         * Optional. The type of source from where ncc can fetch the dependency from
         *
         * @var string|null
         */
        public $SourceType;

        /**
         * Optional. The actual source where NCC can fetch the dependency from
         *
         * @var string|null
         */
        public $Source;

        /**
         * Optional. The required version of the dependency or "latest"
         *
         * @var string|null
         */
        public $Version;

        // TODO: Add validation function here

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            $ReturnResults = [];

            $ReturnResults[($bytecode ? Functions::cbc('name') : 'name')] = $this->Name;

            if($this->SourceType !== null && strlen($this->SourceType) > 0)
                $ReturnResults[($bytecode ? Functions::cbc('source_type') : 'source_type')] = $this->SourceType;

            if($this->Source !== null && strlen($this->Source) > 0)
                $ReturnResults[($bytecode ? Functions::cbc('source') : 'source')] = $this->Source;

            if($this->Version !== null && strlen($this->Version) > 0)
                $ReturnResults[($bytecode ? Functions::cbc('version') : 'version')] = $this->Version;
        
            return $ReturnResults;
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Dependency
         */
        public static function fromArray(array $data): Dependency
        {
            $DependencyObject = new Dependency();

            $DependencyObject->Name = Functions::array_bc($data, 'name');
            $DependencyObject->SourceType = Functions::array_bc($data, 'source_type');
            $DependencyObject->Source = Functions::array_bc($data, 'source');
            $DependencyObject->Version = Functions::array_bc($data, 'version');

            return $DependencyObject;
        }

        /**
         * Validates the dependency configuration
         *
         * @param bool $throw_exception
         * @return bool
         * @throws InvalidDependencyConfiguration
         */
        public function validate(bool $throw_exception): bool
        {
            if(!Validate::packageName($this->Name))
            {
                if($throw_exception)
                    throw new InvalidDependencyConfiguration(sprintf('Invalid dependency name "%s"', $this->Name));

                return false;
            }

            if($this->Version !== null && !Validate::version($this->Version))
            {
                if($throw_exception)
                    throw new InvalidDependencyConfiguration(sprintf('Invalid dependency version "%s"', $this->Version));

                return false;
            }

            return true;
        }
    }