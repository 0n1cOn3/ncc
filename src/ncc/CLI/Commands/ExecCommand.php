<?php

    namespace ncc\CLI\Commands;

    use Exception;
    use ncc\Managers\ExecutionPointerManager;
    use ncc\Objects\CliHelpSection;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\ThirdParty\Symfony\Process\Process;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class ExecCommand
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         */
        public static function start($args): void
        {
            $package = $args['package'] ?? null;
            $version = $args['version'] ?? 'latest';
            $entry = $args['entry'] ?? null;

            if($package == null)
            {
                self::displayOptions();
                exit(0);
            }

            $arguments = [];
            $whitelist_arguments = [
                'package',
                'version',
                'entry',
                'help',
            ];
            foreach($args as $key => $value)
            {
                if(!in_array($key, $whitelist_arguments))
                    $arguments[$key] = $value;
            }

            $execution_pointer_manager = new ExecutionPointerManager();

            try
            {
                $units = $execution_pointer_manager->getUnits($package, $version);
            }
            catch(Exception $e)
            {
                Console::outException(sprintf('Cannot load execution units for package \'%s\'', $package), $e, 1);
                return;
            }

            if(!isset($units[$entry]))
            {
                Console::outError(sprintf('Cannot find execution point \'%s\' in package \'%s\'', $entry, $package), true, 1);
                return;
            }

            /** @var ExecutionUnit $exec_unit */
            $exec_unit = $units[$entry];
            $exec_path = '';

            $process = new Process(array_merge([$exec_path], $arguments));
            if($exec_unit->ExecutionPolicy->Execute->Pty !== null)
                $process->setPty($exec_unit->ExecutionPolicy->Execute->Pty);

            if($exec_unit->ExecutionPolicy->Execute->Tty !== null)
            {
                $process->setTty($exec_unit->ExecutionPolicy->Execute->Tty);
                $process->setPty(false);
            }

            if($exec_unit->ExecutionPolicy->Execute->WorkingDirectory !== null)
                $process->setWorkingDirectory($exec_unit->ExecutionPolicy->Execute->WorkingDirectory);
            if($exec_unit->ExecutionPolicy->Execute->EnvironmentVariables !== null)
                $process->setEnv($exec_unit->ExecutionPolicy->Execute->EnvironmentVariables);
            if($exec_unit->ExecutionPolicy->Execute->Timeout !== null)
                $process->setTimeout($exec_unit->ExecutionPolicy->Execute->Timeout);
            if($exec_unit->ExecutionPolicy->Execute->IdleTimeout !== null)
                $process->setIdleTimeout($exec_unit->ExecutionPolicy->Execute->IdleTimeout);
            if($exec_unit->ExecutionPolicy->Execute->Options !== null)
                $process->setOptions($exec_unit->ExecutionPolicy->Execute->Options);

            if($process->isTty() || $process->isPty())
            {
                $process->start();
                $process->wait();
            }
            else
            {
                $process->start();

                while($process->isRunning())
                {
                    if($exec_unit->ExecutionPolicy->Execute->Silent)
                    {
                        $process->wait();
                    }
                    else
                    {
                        $process->waitUntil(function($type, $buffer)
                        {
                            if($type == Process::ERR)
                            {
                                Console::outError($buffer);
                            }
                            else
                            {
                                Console::out($buffer);
                            }
                        });
                    }
                }
            }

            exit(0);
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
                new CliHelpSection(['exec', '--package'], '(Required) The package to execute'),
                new CliHelpSection(['--version'], '(default: latest) The version of the package to execute'),
                new CliHelpSection(['--entry'], '(default: main) The entry point of the package to execute'),
            ];

            $options_padding = Functions::detectParametersPadding($options) + 4;

            Console::out('Usage: ncc exec --package <package> [options] [arguments]');
            Console::out('Options:' . PHP_EOL);
            foreach($options as $option)
            {
                Console::out('   ' . $option->toString($options_padding));
            }

            Console::out(PHP_EOL . 'Arguments:' . PHP_EOL);
            Console::out('   <arguments>   The arguments to pass to the program');
            Console::out(PHP_EOL . 'Example Usage:' . PHP_EOL);
            Console::out('   ncc exec --package com.example.program');
            Console::out('   ncc exec --package com.example.program --version 1.0.0');
            Console::out('   ncc exec --package com.example.program --version 1.0.0 --entry setup');
            Console::out('   ncc exec --package com.example.program --foo --bar --extra=test');
        }
    }