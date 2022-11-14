<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Objects\ExecutionPointers\ExecutionPointer;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\Assembly;
    use ncc\Utilities\Validate;

    class ExecutionPointers
    {
        /**
         * @var string
         */
        private $Package;

        /**
         * @var string
         */
        private $Version;

        /**
         * @var ExecutionPointer[]
         */
        private $Pointers;

        /**
         * @param Assembly $assembly
         */
        public function __construct(Assembly $assembly)
        {
            $this->Package = $assembly->Package;
            $this->Version = $assembly->Version;
            $this->Pointers = [];
        }

        /**
         * Adds an Execution Unit as a pointer
         *
         * @param ExecutionUnit $unit
         * @param bool $overwrite
         * @return bool
         */
        public function addUnit(ExecutionUnit $unit, bool $overwrite=true): bool
        {
            if(Validate::exceedsPathLength($unit->Data))
                return false;

            if($overwrite)
            {
                $this->deleteUnit($unit->ExecutionPolicy->Name);
            }
            elseif($this->getUnit($unit->ExecutionPolicy->Name) !== null)
            {
                return false;
            }

            $this->Pointers[] = new ExecutionPointer($unit);
            return true;
        }

        /**
         * Deletes an existing unit from execution pointers
         *
         * @param string $name
         * @return bool
         */
        public function deleteUnit(string $name): bool
        {
            $unit = $this->getUnit($name);
            if($unit == null)
                return false;

            $new_pointers = [];
            foreach($this->Pointers as $pointer)
            {
                if($pointer->ExecutionPolicy->Name !== $name)
                    $new_pointers[] = $pointer;
            }

            $this->Pointers = $new_pointers;
            return true;
        }

        /**
         * Returns an existing unit from the pointers
         *
         * @param string $name
         * @return ExecutionPointer|null
         */
        public function getUnit(string $name): ?ExecutionPointer
        {
            foreach($this->Pointers as $pointer)
            {
                if($pointer->ExecutionPolicy->Name == $name)
                    return $pointer;
            }

            return null;
        }

        /**
         * Returns an array of execution pointers that are currently configured
         *
         * @return array|ExecutionPointer[]
         */
        public function getPointers(): array
        {
            return $this->Pointers;
        }

        /**
         * Returns the version of the package that uses these execution policies.
         *
         * @return string
         */
        public function getVersion(): string
        {
            return $this->Version;
        }

        /**
         * Returns the name of the package that uses these execution policies
         *
         * @return string
         */
        public function getPackage(): string
        {
            return $this->Package;
        }
    }