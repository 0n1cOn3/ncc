<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\CLI;

    use Exception;
    use ncc\Abstracts\NccBuildFlags;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\RuntimeException;
    use ncc\ncc;
    use ncc\Utilities\Console;
    use ncc\Utilities\Resolver;

    class Main
    {
        /**
         * @var array
         */
        private static $args;
        
        /**
         * Executes the main CLI process
         *
         * @param $argv
         * @return void
         */
        public static function start($argv): void
        {
            self::$args = Resolver::parseArguments(implode(' ', $argv));

            if(isset(self::$args['ncc-cli']))
            {
                // Initialize NCC
                try
                {
                    ncc::initialize();
                }
                catch (FileNotFoundException $e)
                {
                    Console::outException('Cannot initialize NCC, one or more files were not found.', $e, 1);
                }
                catch (RuntimeException $e)
                {
                    Console::outException('Cannot initialize NCC due to a runtime error.', $e, 1);
                }

                // Define CLI stuff
                define('NCC_CLI_MODE', 1);

                if(in_array(NccBuildFlags::Unstable, NCC_VERSION_FLAGS))
                {
                    Console::outWarning('This is an unstable build of NCC, expect some features to not work as expected');
                }

                try
                {
                    switch(strtolower(self::$args['ncc-cli']))
                    {
                        default:
                            Console::out('Unknown command ' . strtolower(self::$args['ncc-cli']));
                            exit(1);

                        case 'project':
                            ProjectMenu::start(self::$args);
                            exit(0);

                        case 'build':
                            BuildMenu::start(self::$args);
                            exit(0);

                        case 'credential':
                            CredentialMenu::start(self::$args);
                            exit(0);

                        case 'package':
                            PackageManagerMenu::start(self::$args);
                            exit(0);

                        case '1':
                        case 'help':
                            HelpMenu::start(self::$args);
                            exit(0);
                    }
                }
                catch(Exception $e)
                {
                    Console::outException($e->getMessage() . ' (Code: ' . $e->getCode() . ')', $e, 1);
                    exit(1);
                }

            }
        }

        /**
         * @return mixed
         */
        public static function getArgs()
        {
            return self::$args;
        }

    }