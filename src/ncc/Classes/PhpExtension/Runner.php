<?php

    namespace ncc\Classes\PhpExtension;

    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Interfaces\RunnerInterface;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;
    use ncc\Utilities\Base64;
    use ncc\Utilities\IO;
    use ncc\ZiProto\ZiProto;

    class Runner implements RunnerInterface
    {
        /**
         * @param string $path
         * @param ExecutionPolicy $policy
         * @return ExecutionUnit
         * @throws FileNotFoundException
         * @throws AccessDeniedException
         * @throws IOException
         */
        public static function processUnit(string $path, ExecutionPolicy $policy): ExecutionUnit
        {
            $execution_unit = new ExecutionUnit();
            $target_file = $path;
            if(!file_exists($target_file) && !is_file($target_file))
                throw new FileNotFoundException($target_file);
            $policy->Execute->Target = null;
            $execution_unit->ExecutionPolicy = $policy;
            $execution_unit->Data = Base64::encode(IO::fread($target_file));

            return $execution_unit;
        }

        /**
         * @param ExecutionUnit $unit
         * @param string $path
         * @return string
         * @throws IOException
         */
        public static function installUnit(ExecutionUnit $unit, string $path): string
        {
            $script_path = $path . DIRECTORY_SEPARATOR . $unit->getID() . '.php';
            $bin_path = $path . DIRECTORY_SEPARATOR . $unit->getID() . '.bin';
            IO::fwrite($script_path, $unit->Data);
            $unit->Data = $script_path;
            IO::fwrite($bin_path, ZiProto::encode($unit->toArray(true)));

            return $bin_path;
        }
    }