<?php

    namespace ncc\Utilities;

    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\MalformedJsonException;
    use ncc\Objects\CliHelpSection;
    use function chr;
    use function is_int;
    use function is_string;

    /**
     * @author Zi Xing Narrakas
     * @copyright Copyright (C) 2022-2022. Nosial - All Rights Reserved.
     */
    class Functions
    {
        public const FORCE_ARRAY = 0b0001;

        public const PRETTY = 0b0010;

        public const ESCAPE_UNICODE = 0b0100;

        /**
         * Calculates a byte-code representation of the input using CRC32
         *
         * @param string $input
         * @return int
         */
        public static function cbc(string $input): int
        {
            return hexdec(hash('crc32', $input, true));
        }

        /**
         * Returns the specified of a value of an array using plaintext, if none is found it will
         * attempt to use the cbc method to find the selected input, if all fails then null will be returned.
         *
         * @param array $data
         * @param string $select
         * @return mixed|null
         */
        public static function array_bc(array $data, string $select)
        {
            if(isset($data[$select]))
                return $data[$select];

            if(isset($data[self::cbc($select)]))
                return $data[self::cbc($select)];

            return null;
        }

        /**
         * Loads a json file
         *
         * @param string $path
         * @param int $flags
         * @return mixed
         * @throws FileNotFoundException
         * @throws MalformedJsonException
         * @noinspection PhpMissingReturnTypeInspection
         */
        public static function loadJsonFile(string $path, int $flags=0)
        {
            if(!file_exists($path))
            {
                throw new FileNotFoundException($path);
            }

            return self::loadJson(file_get_contents($path), $flags);
        }

        /**
         * Parses a json string
         *
         * @param string $json
         * @param int $flags
         * @return mixed
         * @throws MalformedJsonException
         * @noinspection PhpMissingReturnTypeInspection
         */
        public static function loadJson(string $json, int $flags=0)
        {
            $forceArray = (bool) ($flags & self::FORCE_ARRAY);
            $json_decoded = json_decode($json, $forceArray, 512, JSON_BIGINT_AS_STRING);

            if($json_decoded == null && json_last_error() !== JSON_ERROR_NONE)
            {
                throw new MalformedJsonException(json_last_error_msg() . ' (' . json_last_error() . ')');
            }

            return $json_decoded;
        }

        /**
         * Returns the JSON representation of a value. Accepts flag Json::PRETTY.
         *
         * @param mixed $value
         * @param int $flags
         * @return string
         * @throws MalformedJsonException
         * @noinspection PhpMissingParamTypeInspection
         */
        public static function encodeJson($value, int $flags=0): string
        {
            $flags = ($flags & self::ESCAPE_UNICODE ? 0 : JSON_UNESCAPED_UNICODE)
                | JSON_UNESCAPED_SLASHES
                | ($flags & self::PRETTY ? JSON_PRETTY_PRINT : 0)
                | (defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0); // since PHP 5.6.6 & PECL JSON-C 1.3.7

            $json = json_encode($value, $flags);
            if ($error = json_last_error())
            {
                throw new MalformedJsonException(json_last_error_msg() . ' (' . json_last_error() . ')');
            }
            return $json;
        }

        /**
         * Writes a json file to disk
         *
         * @param $value
         * @param string $path
         * @param int $flags
         * @return void
         * @throws MalformedJsonException
         */
        public static function encodeJsonFile($value, string $path, int $flags=0)
        {
            file_put_contents($path, self::encodeJson($value, $flags));
        }

        /**
         * @param CliHelpSection[] $input
         * @return int
         */
        public static function detectParametersPadding(array $input): int
        {
            $current_count = 0;

            foreach($input as $optionsSection)
            {
                if(count($optionsSection->Parameters) > 0)
                {
                    foreach($optionsSection->Parameters as $parameter)
                    {
                        if($current_count < strlen($parameter))
                        {
                            $current_count = strlen($parameter);
                        }
                    }
                }
            }

            return $current_count;
        }

        /**
         * Returns the banner for the CLI menu (Really fancy stuff!)
         *
         * @param string $version
         * @param string $copyright
         * @param bool $basic_ascii
         * @return string
         */
        public static function getBanner(string $version, string $copyright, bool $basic_ascii=false): string
        {
            if($basic_ascii)
            {
                $banner = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'banner_basic');
            }
            else
            {
                $banner = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'banner_extended');
            }

            $banner_version = str_pad($version, 21);
            $banner_copyright = str_pad($copyright, 30);

            $banner = str_ireplace('%A', $banner_version, $banner);
            /** @noinspection PhpUnnecessaryLocalVariableInspection */
            $banner = str_ireplace('%B', $banner_copyright, $banner);

            return $banner;
        }
    }