<?php

    namespace ncc\Utilities;

    use Exception;
    use ncc\Abstracts\ConsoleColors;
    use ncc\Abstracts\LogLevel;
    use ncc\CLI\Main;
    use ncc\ncc;

    class Console
    {
        /**
         * Inline Progress bar, created by dealnews.com.
         *
         * @param int $value
         * @param int $total
         * @param int $size
         * @param array $options
         * @return void
         *@copyright Copyright (c) 2010, dealnews.com, Inc. All rights reserved.
         */
        public static function inlineProgressBar(int $value, int $total, int $size=38, array $options=[]): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null)
            {
                switch(Main::getLogLevel())
                {
                    case LogLevel::Verbose:
                    case LogLevel::Debug:
                    case LogLevel::Silent:
                        return;

                    default:
                        break;
                }
            }

            static $start_time;

            // if we go over our bound, just ignore it
            if($value > $total)
                return;

            if(empty($start_time)) $start_time=time();
            $now = time();
            $perc=(double)($value/$total);

            $bar=floor($perc*$size);

            $status_bar="\r[ ";
            $status_bar.=str_repeat("=", $bar);
            if($bar<$size){
                $status_bar.=">";
                $status_bar.=str_repeat(" ", $size-$bar);
            } else {
                $status_bar.="=";
            }

            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $disp=number_format($perc*100, 0);

            $status_bar.=" ] $disp%  $value/$total";

            if($value == 0)
                return;

            $rate = ($now-$start_time)/$value;
            $left = $total - $value;
            $eta = round($rate * $left, 2);
            $elapsed = $now - $start_time;

            $remaining_text = 'remaining: ';
            if(isset($options['remaining_text']))
            {
                $remaining_text = $options['remaining_text'];
            }

            $status_bar.= " $remaining_text ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

            echo "$status_bar  ";

            flush();

            // when done, send a newline
            if($value == $total) {
                echo "\n";
            }
        }

        /**
         * Simple output function
         *
         * @param string $message
         * @param bool $newline
         * @return void
         */
        public static function out(string $message, bool $newline=true): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null && !Resolver::checkLogLevel(LogLevel::Info, Main::getLogLevel()))
                return;

            if($newline)
            {
                print($message . PHP_EOL);
                return;
            }

            print($message);
        }

        /**
         * Output debug message
         *
         * @param string $message
         * @param bool $newline
         * @return void
         */
        public static function outDebug(string $message, bool $newline=true): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null && !Resolver::checkLogLevel(LogLevel::Debug, Main::getLogLevel()))
                return;

            self::out(self::formatColor('DBG: ', ConsoleColors::LightMagenta) . $message, $newline);
        }

        /**
         * Output debug message
         *
         * @param string $message
         * @param bool $newline
         * @return void
         */
        public static function outVerbose(string $message, bool $newline=true): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null && !Resolver::checkLogLevel(LogLevel::Verbose, Main::getLogLevel()))
                return;

            self::out(self::formatColor('VRB: ', ConsoleColors::LightCyan) . $message, $newline);
        }


        /**
         * Formats the text to have a different color and returns the formatted value
         *
         * @param string $input The input of the text value
         * @param string $color_code The color code of the escaped character (\e[91m)
         * @param bool $persist If true, the formatting will terminate in the default color
         * @return string
         */
        public static function formatColor(string $input, string $color_code, bool $persist=true): string
        {
            if($persist)
            {
                return $color_code . $input . ConsoleColors::Default;
            }

            return $color_code . $input;
        }

        /**
         * Prints out a warning output
         *
         * @param string $message
         * @param bool $newline
         * @return void
         */
        public static function outWarning(string $message, bool $newline=true): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null && !Resolver::checkLogLevel(LogLevel::Warning, Main::getLogLevel()))
                return;

            self::out(self::formatColor('Warning: ', ConsoleColors::Yellow) . $message, $newline);
        }

        /**
         * Prints out a generic error output, optionally exits the process with an exit code.
         *
         * @param string $message
         * @param bool $newline
         * @param int|null $exit_code
         * @return void
         */
        public static function outError(string $message, bool $newline=true, ?int $exit_code=null): void
        {
            if(!ncc::cliMode())
                return;

            if(Main::getLogLevel() !== null && !Resolver::checkLogLevel(LogLevel::Error, Main::getLogLevel()))
                return;

            self::out(self::formatColor(ConsoleColors::Red, 'Error: ') . $message, $newline);

            if($exit_code !== null)
            {
                exit($exit_code);
            }
        }

        /**
         * Prints out an exception message and exits the program if needed
         *
         * @param string $message
         * @param Exception $e
         * @param int|null $exit_code
         * @return void
         */
        public static function outException(string $message, Exception $e, ?int $exit_code=null): void
        {
            if(!ncc::cliMode())
                return;

            if(strlen($message) > 0 && !Resolver::checkLogLevel(LogLevel::Error, Main::getLogLevel()))
            {
                self::out(PHP_EOL . self::formatColor('Error: ', ConsoleColors::Red) . $message);
            }

            Console::out(PHP_EOL . '===== Exception Details =====');
            self::outExceptionDetails($e);

            if($exit_code !== null)
            {
                exit($exit_code);
            }
        }

        /**
         * Prints out a detailed exception display (unfinished)
         *
         * @param Exception $e
         * @return void
         */
        private static function outExceptionDetails(Exception $e): void
        {
            if(!ncc::cliMode())
                return;

            $trace_header = self::formatColor($e->getFile() . ':' . $e->getLine(), ConsoleColors::Magenta);
            $trace_error = self::formatColor('error: ', ConsoleColors::Red);
            self::out($trace_header . ' ' . $trace_error . $e->getMessage());

            $trace = $e->getTrace();
            if(count($trace) > 1)
            {
                self::out('Stack Trace:');
                foreach($trace as $item)
                {
                    self::out( ' - ' . self::formatColor($item['file'], ConsoleColors::Red) . ':' . $item['line']);
                }
            }

            if(Main::getArgs() !== null)
            {
                if(isset(Main::getArgs()['dbg-ex']))
                {
                    try
                    {
                        $dump = [
                            'constants' => ncc::getConstants(),
                            'exception' => Functions::exceptionToArray($e)
                        ];
                        IO::fwrite(getcwd() . DIRECTORY_SEPARATOR . time() . '.json', json_encode($dump, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 0777);
                    }
                    catch (Exception $e)
                    {
                        self::outWarning('Cannot dump exception details, ' . $e->getMessage());
                    }
                }
                else
                {
                    self::out('You can pass on \'--dbg-ex\' option to dump the exception details to a json file');
                }
            }
        }

        /**
         * @param string|null $prompt
         * @return string
         */
        public static function getInput(?string $prompt=null): string
        {
            if($prompt !== null)
            {
                print($prompt);
            }

            return rtrim(fgets(STDIN), "\n");
        }

        /**
         * @param array $args
         * @param string $option
         * @param string $prompt
         * @return string
         */
        public static function getOptionInput(array $args, string $option, string $prompt): string
        {
            if(isset($args[$option]))
            {
                return $args[$option];
            }

            return self::getInput($prompt);
        }

        /**
         * Prompts the user for a yes/no input
         *
         * @param string $prompt
         * @param bool $display_options
         * @return bool
         */
        public static function getBooleanInput(string $prompt, bool $display_options=true): bool
        {
            while(true)
            {
                if($display_options)
                {
                    $r = self::getInput($prompt . ' (Y/N): ');
                }
                else
                {
                    $r = self::getInput($prompt);
                }

                if(strlen($r) > 0)
                {
                    switch(strtoupper($r))
                    {
                        case '1':
                        case 'Y':
                        case 'YES':
                            return true;

                        case '0':
                        case 'N':
                        case 'NO':
                            return false;
                    }
                }
            }
        }
    }