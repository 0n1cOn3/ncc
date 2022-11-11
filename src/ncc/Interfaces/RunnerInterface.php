<?php

    namespace ncc\Interfaces;

    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Objects\InstallationPaths;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;

    interface RunnerInterface
    {
        /**
         * Processes the ExecutionPolicy
         *
         * @param string $path
         * @param ExecutionPolicy $policy
         * @return ExecutionUnit
         * @throws FileNotFoundException
         * @throws AccessDeniedException
         * @throws IOException
         */
        public static function processUnit(string $path, ExecutionPolicy $policy): ExecutionUnit;

        /**
         * Installs the execution unit onto the system
         *
         * @param ExecutionUnit $unit
         * @param InstallationPaths $paths
         * @return string
         * @throws IOException
         */
        public static function installUnit(ExecutionUnit $unit, InstallationPaths $paths): string;
    }