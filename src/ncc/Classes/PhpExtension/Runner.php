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
            var_dump($target_file);
            if(!file_exists($target_file) && !is_file($target_file))
                throw new FileNotFoundException($target_file);
            $policy->Execute->Target = null;
            $execution_unit->ExecutionPolicy = $policy;
            $execution_unit->Data = Base64::encode(IO::fread($target_file));
            $execution_unit->ID = hash('sha1', $policy->Name, true);

            return $execution_unit;
        }
    }