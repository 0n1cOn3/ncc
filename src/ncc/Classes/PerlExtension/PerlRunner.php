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

namespace ncc\Classes\PerlExtension;

    use ncc\Exceptions\FileNotFoundException;
    use ncc\Interfaces\RunnerInterface;
    use ncc\Objects\Package\ExecutionUnit;
    use ncc\Objects\ProjectConfiguration\ExecutionPolicy;
    use ncc\Utilities\IO;

    class PerlRunner implements RunnerInterface
    {

        /**
         * @inheritDoc
         */
        public static function processUnit(string $path, ExecutionPolicy $policy): ExecutionUnit
        {
            $execution_unit = new ExecutionUnit();
            $policy->Execute->Target = null;
            if(!file_exists($path) && !is_file($path))
                throw new FileNotFoundException($path);
            $execution_unit->ExecutionPolicy = $policy;
            $execution_unit->Data = IO::fread($path);

            return $execution_unit;
        }

        /**
         * @inheritDoc
         */
        public static function getFileExtension(): string
        {
            return '.pl';
        }
    }