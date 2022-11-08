<?php

    namespace ncc\Classes\NccExtension;

    use Exception;
    use ncc\Abstracts\Options\BuildConfigurationValues;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\BuildConfigurationNotFoundException;
    use ncc\Exceptions\BuildException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\UnsupportedRunnerException;
    use ncc\ncc;
    use ncc\Objects\Package;
    use ncc\Objects\ProjectConfiguration;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class PackageCompiler
    {
        /**
         * Compiles the execution policies of the package
         *
         * @param string $path
         * @param ProjectConfiguration $configuration
         * @return array
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws UnsupportedRunnerException
         */
        public static function compileExecutionPolicies(string $path, ProjectConfiguration $configuration): array
        {
            if(count($configuration->ExecutionPolicies) == 0)
                return [];

            Console::out('Compiling Execution Policies');
            $total_items = count($configuration->ExecutionPolicies);
            $execution_units = [];
            $processed_items = 0;

            /** @var ProjectConfiguration\ExecutionPolicy $policy */
            foreach($configuration->ExecutionPolicies as $policy)
            {
                if($total_items > 5)
                {
                    Console::inlineProgressBar($processed_items, $total_items);
                }

                $unit_path = Functions::correctDirectorySeparator($path . $policy->Execute->Target);
                $execution_units[] = Functions::compileRunner($unit_path, $policy);
            }

            if(ncc::cliMode() && $total_items > 5)
                print(PHP_EOL);

            return $execution_units;
        }

        /**
         * Writes the finished package to disk, returns the output path
         *
         * @param string $path
         * @param Package $package
         * @param ProjectConfiguration $configuration
         * @param string $build_configuration
         * @return string
         * @throws BuildConfigurationNotFoundException
         * @throws BuildException
         * @throws IOException
         */
        public static function writePackage(string $path, Package $package, ProjectConfiguration $configuration, string $build_configuration=BuildConfigurationValues::DefaultConfiguration): string
        {
            // Write the package to disk
            $FileSystem = new Filesystem();
            $BuildConfiguration = $configuration->Build->getBuildConfiguration($build_configuration);
            if($FileSystem->exists($path . $BuildConfiguration->OutputPath))
            {
                try
                {
                    $FileSystem->remove($path . $BuildConfiguration->OutputPath);
                }
                catch(\ncc\ThirdParty\Symfony\Filesystem\Exception\IOException $e)
                {
                    throw new BuildException('Cannot delete directory \'' . $path . $BuildConfiguration->OutputPath . '\', ' . $e->getMessage(), $e);
                }
            }

            // Finally write the package to the disk
            $FileSystem->mkdir($path . $BuildConfiguration->OutputPath);
            $output_file = $path . $BuildConfiguration->OutputPath . DIRECTORY_SEPARATOR . $package->Assembly->Package . '.ncc';
            $FileSystem->touch($output_file);

            try
            {
                $package->save($output_file);
            }
            catch(Exception $e)
            {
                throw new IOException('Cannot write to output file', $e);
            }

            return $output_file;
        }
    }