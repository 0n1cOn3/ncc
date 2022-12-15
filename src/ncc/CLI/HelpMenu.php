<?php

    namespace ncc\CLI;

    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Objects\CliHelpSection;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class HelpMenu
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         */
        public static function start($args): void
        {
            $basic_ascii = false;

            if(isset($args['basic-ascii']))
            {
                $basic_ascii = true;
            }

            // TODO: Make copyright not hard-coded.
            print(Functions::getBanner(NCC_VERSION_BRANCH . ' ' . NCC_VERSION_NUMBER, 'Copyright (c) 2022-2022 Nosial', $basic_ascii) . PHP_EOL);

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
            Console::out('Options:');
            Console::outHelpSections([
                new CliHelpSection(['{command} --help'], 'Displays help information about a specific command'),
                new CliHelpSection(['-v', '--version'], 'Display NCC version information'),
                new CliHelpSection(['-D', '--debug'], 'Enables debug mode'),
                new CliHelpSection(['-l', '--log-level={debug|info|warn|error|fatal}'], 'Set the logging level', 'info'),
                new CliHelpSection(['--basic-ascii'], 'Uses basic ascii characters'),
                new CliHelpSection(['--no-color'], 'Omits the use of colors'),
                new CliHelpSection(['--no-banner'], 'Omits displaying the NCC ascii banner')
            ]);
        }

        /**
         * Displays the management commands section
         *
         * @return void
         */
        private static function displayManagementCommands(): void
        {
            Console::out('Management Commands:');
            Console::outHelpSections([
                new CliHelpSection(['project'], 'Manages the current project'),
                new CliHelpSection(['package'], 'Manages the package system'),
                new CliHelpSection(['cache'], 'Manages the system cache'),
                new CliHelpSection(['cred'], 'Manages credentials'),
                new CliHelpSection(['config'], 'Changes NCC configuration values'),
                new CliHelpSection(['source'], 'Manages remote sources'),
            ]);
        }

        /**
         * Displays the main commands section
         *
         * @return void
         */
        private static function displayMainCommands(): void
        {
            Console::out('Commands:');
            Console::outHelpSections([
                new CliHelpSection(['build'], 'Builds the current project'),
                new CliHelpSection(['main'], 'Executes the main entrypoint of a package')
            ]);
        }

        /**
         * Displays the main commands section
         *
         * @return void
         */
        private static function displayExtensions(): void
        {
            Console::out('Extensions:');
            Console::outHelpSections([
                new CliHelpSection(['exphp'], 'The PHP compiler extension')
            ]);
        }
    }