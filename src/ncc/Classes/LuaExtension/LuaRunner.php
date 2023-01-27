<?php

    namespace ncc\Classes\LuaExtension;

    use ncc\Abstracts\Runners;
    use ncc\Interfaces\RunnerInterface;
    use ncc\Objects\ExecutionPointers\ExecutionPointer;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;
    use ncc\ThirdParty\Symfony\Process\Process;
    use ncc\Utilities\Base64;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;

    class LuaRunner implements RunnerInterface
    {

        /**
         * @inheritDoc
         */
        public static function processUnit(string $path, ExecutionPolicy $policy): ExecutionUnit
        {
            $execution_unit = new ExecutionUnit();
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
            return '.lua';
        }
    }