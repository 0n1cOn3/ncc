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

namespace ncc\Classes;

    use ncc\Objects\PhpConfiguration;

    class EnvironmentConfiguration
    {
        /**
         * Returns an array of all the current configuration values set in this environment
         *
         * @return PhpConfiguration[]
         */
        public static function getCurrentConfiguration(): array
        {
            $results = [];

            foreach(ini_get_all() as $name => $config)
            {
                $results[$name] = PhpConfiguration::fromArray($config);
            }

            return $results;
        }

        /**
         * Returns an array of only the changed configuration values
         *
         * @return PhpConfiguration[]
         */
        public static function getChangedValues(): array
        {
            $results = [];

            foreach(ini_get_all() as $name => $config)
            {
                $config = PhpConfiguration::fromArray($config);
                if($config->LocalValue !== $config->GlobalValue)
                {
                    $results[$name] = $config;
                }
            }

            return $results;
        }

        /**
         * @param string $file_path
         * @return void
         */
        public static function export(string $file_path)
        {
            $configuration = [];
            foreach(self::getChangedValues() as $changedValue)
            {
                $configuration[$changedValue->getName()] = $changedValue->getValue();
            }

            // TODO: Implement ini writing process here
        }

        public static function import(string $file_path)
        {
            // TODO: Implement ini reading process here
            $configuration = [];
            foreach($configuration as $item => $value)
            {
                ini_set($item, $value);
            }
        }
    }