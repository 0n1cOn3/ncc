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

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use InvalidArgumentException;
    use ncc\Enums\RegexPatterns;

    class RemotePackageInput
    {
        /**
         * @var string
         */
        private $vendor;

        /**
         * @var string
         */
        private $package;

        /**
         * @var string|null
         */
        private $version;

        /**
         * @var string|null
         */
        private $branch;

        /**
         * @var string
         */
        private $source;

        /**
         * Public Constructor & String Parser
         *
         * @param string|null $input
         */
        public function __construct(?string $input = null)
        {
            if ($input !== null && preg_match(RegexPatterns::REMOTE_PACKAGE, $input, $matches))
            {
                if ($matches['source'] === null || $matches['package'] === null || $matches['vendor'] === null)
                {
                    throw new InvalidArgumentException('Package, version, and source are required.');
                }

                $this->vendor = $matches['vendor'];
                $this->package = $matches['package'];
                $this->source = $matches['source'];
                $this->version = empty($matches['version']) ? null : $matches['version'];
                $this->branch = empty($matches['branch']) ? null : $matches['branch'];
            }
            else
            {
                throw new InvalidArgumentException(sprintf('Invalid remote package input: %s', $input));
            }
        }

        /**
         * @return string
         */
        public function getVendor(): string
        {
            return $this->vendor;
        }

        /**
         * @param string $vendor
         */
        public function setVendor(string $vendor): void
        {
            $this->vendor = $vendor;
        }

        /**
         * @return string
         */
        public function getPackage(): string
        {
            return $this->package;
        }

        /**
         * @param string $package
         */
        public function setPackage(string $package): void
        {
            $this->package = $package;
        }

        /**
         * @return string|null
         */
        public function getVersion(): ?string
        {
            return $this->version;
        }

        /**
         * @param string|null $version
         */
        public function setVersion(?string $version): void
        {
            $this->version = $version;
        }

        /**
         * @return string|null
         */
        public function getBranch(): ?string
        {
            return $this->branch;
        }

        /**
         * @param string|null $branch
         */
        public function setBranch(?string $branch): void
        {
            $this->branch = $branch;
        }

        /**
         * @return string
         */
        public function getSource(): string
        {
            return $this->source;
        }

        /**
         * @param string $source
         */
        public function setSource(string $source): void
        {
            $this->source = $source;
        }

        /**
         * Returns a string representation of the input
         *
         * @return string
         */
        public function toString(): string
        {
            if($this->vendor === null || $this->package === null)
            {
                return '';
            }

            $results = $this->vendor . '/' . $this->package;

            if($this->version !== null)
            {
                $results .= '=' . $this->version;
            }

            if($this->branch !== null)
            {
                $results .= ':' . $this->branch;
            }

            if($this->source !== null)
            {
                $results .= '@' . $this->source;
            }

            return $results;
        }

        /**
         * Returns a standard package name string representation
         *
         * @param bool $version
         * @return string
         */
        public function toStandard(bool $version=true): string
        {
            if($version)
            {
                return str_replace('-', '_', sprintf('com.%s.%s=%s', $this->vendor, $this->package, $this->version));
            }

            return str_replace('-', '_', sprintf('com.%s.%s', $this->vendor, $this->package));
        }
    }