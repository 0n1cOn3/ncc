<?php

    namespace ncc\CLI;

    use Exception;
    use ncc\Abstracts\ConsoleColors;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Managers\PackageManager;
    use ncc\Objects\CliHelpSection;
    use ncc\Objects\Package;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;
    use ncc\Utilities\Resolver;

    class PackageManagerMenu
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         */
        public static function start($args): void
        {
            if(isset($args['install']))
            {
                try
                {
                    self::installPackage($args);
                    return;
                }
                catch (Exception $e)
                {
                    Console::outException('Installation Failed', $e, 1);
                    return;
                }
            }

            if(isset($args['list']))
            {
                try
                {
                    self::getInstallPackages();
                    return;
                }
                catch(Exception $e)
                {
                    Console::outException('List Failed', $e, 1);
                    return;
                }
            }

            self::displayOptions();
            exit(0);
        }

        /**
         * Displays all installed packages
         *
         * @return void
         */
        private static function getInstallPackages(): void
        {
            $package_manager = new PackageManager();

            try
            {
                $installed_packages = $package_manager->getInstalledPackages();
            }
            catch (Exception $e)
            {
                Console::out('No packages installed');
                exit(0);
            }

            foreach($installed_packages as $package => $versions)
            {
                if(count($versions) == 0)
                {
                    continue;
                }

                foreach($versions as $version)
                {
                    try
                    {
                        $package_version = $package_manager->getPackageVersion($package, $package);
                        if($package_version == null)
                            throw new Exception();

                        Console::out(sprintf('%s==%s (%s)',
                            Console::formatColor($package, ConsoleColors::LightGreen),
                            Console::formatColor($version, ConsoleColors::LightMagenta),
                            $package_manager->getPackageVersion($package, $package)->Compiler->Extension
                        ));
                    }
                    catch(Exception $e)
                    {
                        unset($e);
                        Console::out(sprintf('%s==%s',
                            Console::formatColor($package, ConsoleColors::LightGreen),
                            Console::formatColor($version, ConsoleColors::LightMagenta)
                        ));
                    }
                }
            }
        }

        /**
         * @param $args
         * @return void
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         */
        private static function installPackage($args): void
        {
            $path = ($args['path'] ?? $args['p']);
            $package_manager = new PackageManager();

            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Insufficient permission to install packages');

            if(!file_exists($path) || !is_file($path) || !is_readable($path))
                throw new FileNotFoundException('The specified file \'' . $path .' \' does not exist or is not readable.');

            $user_confirmation = false;
            if(isset($args['y']) || isset($args['Y']))
            {
                $user_confirmation = (bool)($args['y'] ?? $args['Y']);
            }

            try
            {
                $package = Package::load($path);
            }
            catch(Exception $e)
            {
                Console::outException('Error while loading package', $e, 1);
                return;
            }

            Console::out('Package installation details' . PHP_EOL);
            if(!is_null($package->Assembly->UUID))
                Console::out('  UUID: ' . Console::formatColor($package->Assembly->UUID, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Package))
                Console::out('  Package: ' . Console::formatColor($package->Assembly->Package, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Name))
                Console::out('  Name: ' . Console::formatColor($package->Assembly->Name, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Version))
                Console::out('  Version: ' . Console::formatColor($package->Assembly->Version, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Description))
                Console::out('  Description: ' . Console::formatColor($package->Assembly->Description, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Product))
                Console::out('  Product: ' . Console::formatColor($package->Assembly->Product, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Company))
                Console::out('  Company: ' . Console::formatColor($package->Assembly->Company, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Copyright))
                Console::out('  Copyright: ' . Console::formatColor($package->Assembly->Copyright, ConsoleColors::LightGreen));
            if(!is_null($package->Assembly->Trademark))
                Console::out('  Trademark: ' . Console::formatColor($package->Assembly->Trademark, ConsoleColors::LightGreen));
            Console::out((string)null);

            if(count($package->Dependencies) > 0)
            {
                $dependencies = [];
                foreach($package->Dependencies as $dependency)
                {
                    $dependencies[] = sprintf('%s v%s',
                        Console::formatColor($dependency->Name, ConsoleColors::Green),
                        Console::formatColor($dependency->Version, ConsoleColors::LightMagenta)
                    );
                }

                Console::out('The following dependencies will be installed:');
                Console::out(sprintf('  %s', implode(', ', $dependencies)) . PHP_EOL);
            }

            Console::out(sprintf('Extension: %s',
                Console::formatColor($package->Header->CompilerExtension->Extension, ConsoleColors::Green)
            ));

            if($package->Header->CompilerExtension->MaximumVersion !== null)
                Console::out(sprintf('Maximum Version: %s',
                    Console::formatColor($package->Header->CompilerExtension->MaximumVersion, ConsoleColors::LightMagenta)
                ));

            if($package->Header->CompilerExtension->MinimumVersion !== null)
                Console::out(sprintf('Minimum Version: %s',
                    Console::formatColor($package->Header->CompilerExtension->MinimumVersion, ConsoleColors::LightMagenta)
                ));

            if(!$user_confirmation)
                $user_confirmation = Console::getBooleanInput(sprintf('Do you want to install %s', $package->Assembly->Package));

            if($user_confirmation)
            {
                try
                {
                    $package_manager->install($path);
                    return;
                }
                catch(Exception $e)
                {
                    Console::outException('Installation Failed', $e, 1);
                }

            }

            Console::outError('User cancelled installation', true, 1);
        }

        /**
         * Displays the main options section
         *
         * @return void
         */
        private static function displayOptions(): void
        {
            $options = [
                new CliHelpSection(['help'], 'Displays this help menu about the value command'),
                new CliHelpSection(['install', '--path', '-p'], 'Installs a specified NCC package file'),
                new CliHelpSection(['list'], 'Lists all installed packages on the system'),
            ];

            $options_padding = Functions::detectParametersPadding($options) + 4;

            Console::out('Usage: ncc install {command} [options]');
            Console::out('Options:' . PHP_EOL);
            foreach($options as $option)
            {
                Console::out('   ' . $option->toString($options_padding));
            }
        }
    }