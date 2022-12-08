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

    class PythonRunner implements RunnerInterface
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
            $execution_unit->Data = Base64::encode(IO::fread($path));

            return $execution_unit;
        }

        /**
         * @inheritDoc
         */
        public static function getFileExtension(): string
        {
            return '.py';
        }

        /**
         * @inheritDoc
         */
        public static function prepareProcess(ExecutionPointer $pointer): Process
        {
            $python_bin = PathFinder::findRunner(Runners::python);

            if($pointer->ExecutionPolicy->Execute->Options !== null && count($pointer->ExecutionPolicy->Execute->Options) > 0)
                return new Process(array_merge([$python_bin, $pointer->FilePointer], $pointer->ExecutionPolicy->Execute->Options));
            return new Process([$python_bin, $pointer->FilePointer]);
        }
}