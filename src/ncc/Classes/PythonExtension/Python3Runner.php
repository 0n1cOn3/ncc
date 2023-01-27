<?php

    namespace ncc\Classes\PythonExtension;

    use ncc\Abstracts\Runners;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Interfaces\RunnerInterface;
    use ncc\Objects\ExecutionPointers\ExecutionPointer;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;
    use ncc\ThirdParty\Symfony\Process\Process;
    use ncc\Utilities\Base64;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;

    class Python3Runner implements RunnerInterface
    {

        /**
         * @inheritDoc
         */
        public static function processUnit(string $path, ExecutionPolicy $policy): ExecutionUnit
        {
            $execution_unit = new ExecutionUnit();
            if(!file_exists($path) && !is_file($path))
                throw new FileNotFoundException($path);
            $policy->Execute->Target = null;
            $execution_unit->ExecutionPolicy = $policy;
            $execution_unit->Data = IO::fread($path);

            return $execution_unit;
        }

        /**
         * @inheritDoc
         */
        public static function getFileExtension(): string
        {
            return '.py';
        }
    }