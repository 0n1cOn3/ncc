<?php

    namespace ncc\Classes\NccExtension;

    use Exception;
    use ncc\Abstracts\CompilerExtensions;
    use ncc\Abstracts\Options\BuildConfigurationValues;
    use ncc\Classes\PhpExtension\Compiler;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\BuildConfigurationNotFoundException;
    use ncc\Exceptions\BuildException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\MalformedJsonException;
    use ncc\Exceptions\PackagePreparationFailedException;
    use ncc\Exceptions\ProjectConfigurationNotFoundException;
    use ncc\Exceptions\UnsupportedCompilerExtensionException;
    use ncc\Exceptions\UnsupportedRunnerException;
    use ncc\Interfaces\CompilerInterface;
    use ncc\Managers\ProjectManager;
    use ncc\ncc;
    use ncc\Objects\Package;
    use ncc\Objects\ProjectConfiguration;
    use ncc\Objects\ProjectConfiguration\Assembly;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class PackageCompiler
    {
        /**
         * Compiles the project into a package
         *
         * @param ProjectManager $manager
         * @param string $build_configuration
         * @return string
         * @throws AccessDeniedException
         * @throws BuildConfigurationNotFoundException
         * @throws BuildException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws MalformedJsonException
         * @throws PackagePreparationFailedException
         * @throws ProjectConfigurationNotFoundException
         * @throws UnsupportedCompilerExtensionException
         * @throws UnsupportedRunnerException
         */
        public static function compile(ProjectManager $manager, string $build_configuration=BuildConfigurationValues::DefaultConfiguration): string
        {
            $configuration = $manager->getProjectConfiguration();

            // Select the correct compiler for the specified extension
            switch(strtolower($configuration->Project->Compiler->Extension))
            {
                case CompilerExtensions::PHP:
                    /** @var CompilerInterface $Compiler */
                    $Compiler = new Compiler($configuration, $manager->getProjectPath());
                    break;

                default:
                    throw new UnsupportedCompilerExtensionException('The compiler extension \'' . $configuration->Project->Compiler->Extension . '\' is not supported');
            }

            $build_configuration = $configuration->Build->getBuildConfiguration($build_configuration)->Name;
            $Compiler->prepare($build_configuration);
            $Compiler->build();

            return PackageCompiler::writePackage(
                $manager->getProjectPath(), $Compiler->getPackage(), $configuration, $build_configuration
            );
        }

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

        /**
         * Compiles the special formatted constants
         *
         * @param Package $package
         * @param ProjectConfiguration $project_configuration
         * @param int $timestamp
         * @return array
         */
        public static function compileRuntimeConstants(Package $package, ProjectConfiguration $project_configuration, int $timestamp): array
        {
            $compiled_constants = [];
            foreach($package->Header->RuntimeConstants as $name => $value)
            {
                $compiled_constants[$name] = self::regularConstants($value, $package, $timestamp);
            }
            return $compiled_constants;
        }

        /**
         * Compiles the constants in the package object
         *
         * @param Package $package
         * @param int $timestamp
         * @return void
         */
        public static function compilePackageConstants(Package &$package, int $timestamp): void
        {
            $assembly = [];
            foreach($package->Assembly->toArray() as $key => $value)
            {
                $assembly[$key] = self::regularConstants($value, $package, $timestamp);
            }
            $package->Assembly = Assembly::fromArray($assembly);

            foreach($package->ExecutionUnits as $executionUnit)
            {

            }

            unset($assembly);
        }

        /**
         * Compiles regular constants
         *
         * @param string|null $value
         * @param Package $package
         * @param int $timestamp
         * @return string|null
         * @noinspection PhpUnnecessaryLocalVariableInspection
         */
        private static function regularConstants(?string $value, Package $package, int $timestamp): ?string
        {
            if($value == null)
                return null;

            $value = ConstantCompiler::compileAssemblyConstants($value, $package->Assembly);
            $value = ConstantCompiler::compileBuildConstants($value);
            $value = ConstantCompiler::compileDateTimeConstants($value, $timestamp);

            return $value;
        }
    }