<?php

    namespace ncc\CLI;

    use ncc\Managers\PackageManager;
    use ncc\Objects\CliHelpSection;
    use ncc\Utilities\Console;

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
                self::installPackage($args);
            }

            self::displayOptions();
            exit(0);
        }

        private static function installPackage($args): void
        {
            $path = ($args['path'] ?? $args['p']);
            $package_manager = new PackageManager();
            var_dump($package_manager->install($path));
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
            ];

            $options_padding = \ncc\Utilities\Functions::detectParametersPadding($options) + 4;

            Console::out('Usage: ncc install {command} [options]');
            Console::out('Options:' . PHP_EOL);
            foreach($options as $option)
            {
                Console::out('   ' . $option->toString($options_padding));
            }
        }
    }