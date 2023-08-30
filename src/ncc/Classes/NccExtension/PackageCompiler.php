<?php
    /*
     * Copyright (c) Nosial 2022-2023, all rights reserved.
     *
     *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
     *  associated documentation files (the "Software"), to deal in the Software without restriction, including without
     *  limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
     *  Software, and to permit persons to whom the Software is furnished to do so, subject to the following
     *  conditions:
     *
     *  The above copyright notice and this permission notice shall be included in all copies or substantial portions
     *  of the Software.
     *
     *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
     *  INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
     *  PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
     *  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
     *  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
     *  DEALINGS IN THE SOFTWARE.
     *
     */

    namespace ncc\Classes\NccExtension;

    use Exception;
    use ncc\Enums\CompilerExtensions;
    use ncc\Enums\ConstantReferences;
    use ncc\Enums\LogLevel;
    use ncc\Enums\Options\BuildConfigurationValues;
    use ncc\Enums\ProjectType;
    use ncc\Classes\ComposerExtension\ComposerSourceBuiltin;
    use ncc\Classes\PhpExtension\PhpCompiler;
    use ncc\CLI\Main;
    use ncc\Exceptions\BuildException;
    use ncc\Exceptions\ConfigurationException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\NotSupportedException;
    use ncc\Exceptions\PathNotFoundException;
    use ncc\Interfaces\CompilerInterface;
    use ncc\Managers\ProjectManager;
    use ncc\ncc;
    use ncc\Objects\Package;
    use ncc\Objects\ProjectConfiguration;
    use ncc\Objects\ProjectConfiguration\Assembly;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;
    use ncc\Utilities\Resolver;

    class PackageCompiler
    {
        /**
         * Compiles the project into a package
         *
         * @param ProjectManager $manager
         * @param string $build_configuration
         * @return string
         * @throws BuildException
         * @throws ConfigurationException
         * @throws IOException
         * @throws NotSupportedException
         * @throws PathNotFoundException
         */
        public static function compile(ProjectManager $manager, string $build_configuration=BuildConfigurationValues::DEFAULT): string
        {
            $configuration = $manager->getProjectConfiguration();

            if(Resolver::checkLogLevel(LogLevel::DEBUG, Main::getLogLevel()))
            {
                foreach($configuration->getAssembly()->toArray() as $prop => $value)
                {
                    Console::outDebug(sprintf('assembly.%s: %s', $prop, ($value ?? 'n/a')));
                }
                foreach($configuration->getProject()->getCompiler()->toArray() as $prop => $value)
                {
                    Console::outDebug(sprintf('compiler.%s: %s', $prop, ($value ?? 'n/a')));
                }
            }

            // Select the correct compiler for the specified extension
            if (strtolower($configuration->getProject()->getCompiler()->getExtension()) === CompilerExtensions::PHP)
            {
                /** @var CompilerInterface $Compiler */
                $Compiler = new PhpCompiler($configuration, $manager->getProjectPath());
            }
            else
            {
                throw new NotSupportedException('The compiler extension \'' . $configuration->getProject()->getCompiler()->getExtension() . '\' is not supported');
            }

            $build_configuration = $configuration->getBuild()->getBuildConfiguration($build_configuration)->getName();
            Console::out(sprintf('Building %s=%s', $configuration->getAssembly()->getPackage(), $configuration->getAssembly()->getVersion()));
            $Compiler->prepare($build_configuration);
            $Compiler->build();

            return self::writePackage(
                $manager->getProjectPath(), $Compiler->getPackage(), $configuration, $build_configuration
            );
        }

        /**
         * Attempts to detect the project type and convert it accordingly before compiling
         * Returns the compiled package path
         *
         * @param string $path
         * @param string|null $version
         * @return string
         * @throws BuildException
         */
        public static function tryCompile(string $path, ?string $version=null): string
        {
            $project_type = Resolver::detectProjectType($path);

            try
            {
                if($project_type->getProjectType() === ProjectType::COMPOSER)
                {
                    $project_path = ComposerSourceBuiltin::fromLocal($project_type->getProjectPath());
                }
                elseif($project_type->getProjectType() === ProjectType::NCC)
                {
                    $project_manager = new ProjectManager($project_type->getProjectPath());
                    $project_manager->getProjectConfiguration()->getAssembly()->setVersion($version);
                    $project_path = $project_manager->build();
                }
                else
                {
                    throw new NotSupportedException(sprintf('Failed to compile %s, project type %s is not supported', $project_type->getProjectPath(), $project_type->getProjectType()));
                }

                if($version !== null)
                {
                    $package = Package::load($project_path);
                    $package->getAssembly()->setVersion(Functions::convertToSemVer($version));
                    $package->save($project_path);
                }

                return $project_path;
            }
            catch(Exception $e)
            {
                throw new BuildException('Failed to build project', $e);
            }
        }


        /**
         * Compiles the execution policies of the package
         *
         * @param string $path
         * @param ProjectConfiguration $configuration
         * @return array
         * @throws IOException
         * @throws NotSupportedException
         * @throws PathNotFoundException
         */
        public static function compileExecutionPolicies(string $path, ProjectConfiguration $configuration): array
        {
            if(count($configuration->getExecutionPolicies()) === 0)
            {
                return [];
            }

            Console::out('Compiling Execution Policies');
            $total_items = count($configuration->getExecutionPolicies());
            $execution_units = [];
            $processed_items = 1;

            /** @var ProjectConfiguration\ExecutionPolicy $policy */
            foreach($configuration->getExecutionPolicies() as $policy)
            {
                Console::outVerbose(sprintf('Compiling Execution Policy %s', $policy->getName()));

                /** @noinspection DisconnectedForeachInstructionInspection */
                if($total_items > 5)
                {
                    Console::inlineProgressBar($processed_items, $total_items);
                }

                $unit_path = Functions::correctDirectorySeparator($path . $policy->getExecute()->getTarget());
                $execution_units[] = Functions::compileRunner($unit_path, $policy);
            }

            if($total_items > 5 && ncc::cliMode())
            {
                print(PHP_EOL);
            }

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
         * @throws IOException
         * @throws ConfigurationException
         */
        public static function writePackage(string $path, Package $package, ProjectConfiguration $configuration, string $build_configuration=BuildConfigurationValues::DEFAULT): string
        {
            Console::outVerbose(sprintf('Writing package to %s', $path));

            // Write the package to disk
            $FileSystem = new Filesystem();
            $BuildConfiguration = $configuration->getBuild()->getBuildConfiguration($build_configuration);
            if(!$FileSystem->exists($path . $BuildConfiguration->getOutputPath()))
            {
                Console::outDebug(sprintf('creating output directory %s', $path . $BuildConfiguration->getOutputPath()));
                $FileSystem->mkdir($path . $BuildConfiguration->getOutputPath());
            }

            // Finally write the package to the disk
            $FileSystem->mkdir($path . $BuildConfiguration->getOutputPath());
            $output_file = $path . $BuildConfiguration->getOutputPath() . DIRECTORY_SEPARATOR . $package->getAssembly()->getPackage() . '.ncc';
            if($FileSystem->exists($output_file))
            {
                Console::outDebug(sprintf('removing existing package %s', $output_file));
                $FileSystem->remove($output_file);
            }
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
         * Compiles the constants in the package object
         *
         * @param Package $package
         * @param array $refs
         * @return void
         */
        public static function compilePackageConstants(Package $package, array $refs): void
        {
            if($package->getAssembly() !== null)
            {
                $assembly = [];

                foreach($package->getAssembly()->toArray() as $key => $value)
                {
                    Console::outDebug(sprintf('compiling constant Assembly.%s (%s)', $key, implode(', ', array_keys($refs))));
                    $assembly[$key] = self::compileConstants($value, $refs);
                }
                $package->setAssembly(Assembly::fromArray($assembly));

                unset($assembly);
            }

            if(count($package->getExecutionUnits()) > 0)
            {
                $units = [];
                foreach($package->ExecutionUnits() as $executionUnit)
                {
                    Console::outDebug(sprintf('compiling execution unit constant %s (%s)', $executionUnit->getExecutionPolicy()->getName(), implode(', ', array_keys($refs))));
                    $units[] = self::compileExecutionUnitConstants($executionUnit, $refs);
                }
                $package->setExecutionUnits($units);
                unset($units);
            }

            $compiled_constants = [];
            foreach($package->getHeader()->getRuntimeConstants() as $name => $value)
            {
                Console::outDebug(sprintf('compiling runtime constant %s (%s)', $name, implode(', ', array_keys($refs))));
                $compiled_constants[$name] = self::compileConstants($value, $refs);
            }

            $options = [];
            foreach($package->getHeader()->getOptions() as $name => $value)
            {
                if(is_array($value))
                {
                    $options[$name] = [];
                    foreach($value as $key => $val)
                    {
                        if(!is_string($val))
                        {
                            continue;
                        }

                        Console::outDebug(sprintf('compiling option %s.%s (%s)', $name, $key, implode(', ', array_keys($refs))));
                        $options[$name][$key] = self::compileConstants($val, $refs);
                    }
                }
                else
                {
                    Console::outDebug(sprintf('compiling option %s (%s)', $name, implode(', ', array_keys($refs))));
                    $options[$name] = self::compileConstants((string)$value, $refs);
                }
            }

            $package->getHeader()->setOptions($options);
            $package->getHeader()->setRuntimeConstants($compiled_constants);
        }

        /**
         * Compiles the constants in a given execution unit
         *
         * @param Package\ExecutionUnit $unit
         * @param array $refs
         * @return Package\ExecutionUnit
         */
        public static function compileExecutionUnitConstants(Package\ExecutionUnit $unit, array $refs): Package\ExecutionUnit
        {
            $unit->getExecutionPolicy()->setMessage(self::compileConstants($unit->getExecutionPolicy()->getMessage(), $refs));

            if($unit->getExecutionPolicy()->getExitHandlers() !== null)
            {
                if($unit->getExecutionPolicy()->getExitHandlers()->getSuccess()?->getMessage() !== null)
                {
                    $unit->getExecutionPolicy()->getExitHandlers()->getSuccess()?->setMessage(
                        self::compileConstants($unit->getExecutionPolicy()->getExitHandlers()->getSuccess()->getMessage(), $refs)
                    );
                }

                if($unit->getExecutionPolicy()->getExitHandlers()->getError()?->getMessage() !== null)
                {
                    $unit->getExecutionPolicy()->getExitHandlers()->getError()?->setMessage(
                        self::compileConstants($unit->getExecutionPolicy()->getExitHandlers()->getError()->getMessage(), $refs)
                    );
                }

                if($unit->getExecutionPolicy()->getExitHandlers()->getWarning()?->getMessage() !== null)
                {
                    $unit->getExecutionPolicy()->getExitHandlers()->getWarning()?->setMessage(
                        self::compileConstants($unit->getExecutionPolicy()->getExitHandlers()->getWarning()->getMessage(), $refs)
                    );
                }

            }

            if($unit->getExecutionPolicy()->getExecute() !== null)
            {
                $unit->getExecutionPolicy()->getExecute()->setTarget(self::compileConstants($unit->getExecutionPolicy()->getExecute()->getTarget(), $refs));
                $unit->getExecutionPolicy()->getExecute()->setWorkingDirectory(self::compileConstants($unit->getExecutionPolicy()->getExecute()->getWorkingDirectory(), $refs));

                if(count($unit->getExecutionPolicy()->getExecute()->getOptions()) > 0)
                {
                    $options = [];
                    foreach($unit->getExecutionPolicy()->getExecute()->getOptions() as $key=> $value)
                    {
                        $options[self::compileConstants($key, $refs)] = self::compileConstants($value, $refs);
                    }

                    $unit->getExecutionPolicy()->getExecute()->setOptions($options);
                }
            }

            return $unit;
        }

        /**
         * Compiles multiple types of constants
         *
         * @param string|null $value
         * @param array $refs
         * @return string|null
         */
        public static function compileConstants(?string $value, array $refs): ?string
        {
            if($value === null)
            {
                return null;
            }

            if(isset($refs[ConstantReferences::ASSEMBLY]))
            {
                $value = ConstantCompiler::compileAssemblyConstants($value, $refs[ConstantReferences::ASSEMBLY]);
            }

            if(isset($refs[ConstantReferences::BUILD]))
            {
                $value = ConstantCompiler::compileBuildConstants($value);
            }

            if(isset($refs[ConstantReferences::DATE_TIME]))
            {
                $value = ConstantCompiler::compileDateTimeConstants($value, $refs[ConstantReferences::DATE_TIME]);
            }

            if(isset($refs[ConstantReferences::INSTALL]))
            {
                $value = ConstantCompiler::compileInstallConstants($value, $refs[ConstantReferences::INSTALL]);
            }

            if(isset($refs[ConstantReferences::RUNTIME]))
            {
                $value = ConstantCompiler::compileRuntimeConstants($value);
            }

            return $value;
        }
    }