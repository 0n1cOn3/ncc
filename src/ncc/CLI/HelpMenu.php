<?php

    namespace ncc\CLI;

    use ncc\Objects\CliHelpSection;
    use ncc\Utilities\Console;

    class HelpMenu
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         */
        public static function start($args)
        {
            $basic_ascii = false;

            if(isset($args['basic-ascii']))
            {
                $basic_ascii = true;
            }

            // TODO: Make copyright not hard-coded.
            print(\ncc\Utilities\Functions::getBanner(NCC_VERSION_BRANCH . ' ' . NCC_VERSION_NUMBER, 'Copyright (c) 2022-2022 Nosial', $basic_ascii) . PHP_EOL);

            Console::out('Usage: ncc COMMAND [options]');
            Console::out('Alternative Usage: ncc.php --ncc-cli=COMMAND [options]' . PHP_EOL);
            Console::out('Nosial Code Compiler / Project Toolkit' . PHP_EOL);

            self::displayMainOptions();
            self::displayManagementCommands();
            self::displayMainCommands();
            self::displayExtensions();
        }

        /**
         * Displays the main options section
         *
         * @return void
         */
        private static function displayMainOptions(): void
        {
            $options = [
                new CliHelpSection(['{command} --help'], 'Displays help information about a specific command'),
                new CliHelpSection(['-v', '--version'], 'Display NCC version information'),
                new CliHelpSection(['-D', '--debug'], 'Enables debug mode'),
                new CliHelpSection(['-l', '--log-level={debug|info|warn|error|fatal}'], 'Set the logging level', 'info'),
                new CliHelpSection(['--basic-ascii'], 'Uses basic ascii characters'),
                new CliHelpSection(['--no-color'], 'Omits the use of colors'),
                new CliHelpSection(['--no-banner'], 'Omits displaying the NCC ascii banner')
            ];
            $options_padding = \ncc\Utilities\Functions::detectParametersPadding($options) + 4;

            Console::out('Options:');
            foreach($options as $option)
            {
                Console::out('   ' . $option->toString($options_padding));
            }
        }

        /**
         * Displays the management commands section
         *
         * @return void
         */
        private static function displayManagementCommands(): void
        {
            $commands = [
                new CliHelpSection(['project'], 'Manages the current project'),
                new CliHelpSection(['package'], 'Manages the package system'),
                new CliHelpSection(['cache'], 'Manages the system cache'),
                new CliHelpSection(['cred'], 'Manages credentials'),
                new CliHelpSection(['config'], 'Changes NCC configuration values'),
            ];
            $commands_padding = \ncc\Utilities\Functions::detectParametersPadding($commands) + 2;

            Console::out('Management Commands:');
            foreach($commands as $command)
            {
                Console::out('   ' . $command->toString($commands_padding));
            }
        }

        /**
         * Displays the main commands section
         *
         * @return void
         */
        private static function displayMainCommands(): void
        {
            $commands = [
                new CliHelpSection(['build'], 'Builds the current project'),
                new CliHelpSection(['main'], 'Executes the main entrypoint of a package')
            ];
            $commands_padding = \ncc\Utilities\Functions::detectParametersPadding($commands) + 2;

            Console::out('Commands:');
            foreach($commands as $command)
            {
                Console::out('   ' . $command->toString($commands_padding));
            }
        }

        /**
         * Displays the main commands section
         *
         * @return void
         */
        private static function displayExtensions(): void
        {
            $extensions = [
                new CliHelpSection(['exphp'], 'The PHP compiler extension')
            ];
            $extensions_padding = \ncc\Utilities\Functions::detectParametersPadding($extensions) + 2;

            Console::out('Extensions:');
            foreach($extensions as $command)
            {
                Console::out('   ' . $command->toString($extensions_padding));
            }
        }
    }