<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Managers;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Objects\PackageLock;
    use ncc\Utilities\IO;
    use ncc\Utilities\Resolver;
    use ncc\ZiProto\ZiProto;

    class PackageLockManager
    {
        /**
         * @var PackageLock|null
         */
        private $PackageLock;

        /**
         * @var string
         */
        private $PackageLockPath;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            if(file_exists($this->PackageLockPath) && is_file($this->PackageLockPath))
            {
                try
                {
                    $this->PackageLock = PackageLock::fromArray(ZiProto::decode(IO::fread($this->PackageLockPath)));
                }
                /** @noinspection PhpUnusedLocalVariableInspection */
                catch(Exception $e)
                {
                    $this->PackageLock = new PackageLock();
                }
            }
            else
            {
                $this->PackageLock = new PackageLock();
            }
        }

        /**
         * Loads the PackageLock from the disk
         *
         * @return void
         * @throws PackageLockException
         */
        public function load(): void
        {
            if(file_exists($this->PackageLockPath) && is_file($this->PackageLockPath))
            {
                try
                {
                    $this->PackageLock = PackageLock::fromArray(ZiProto::decode(IO::fread($this->PackageLockPath)));
                }
                catch(Exception $e)
                {
                    throw new PackageLockException('The PackageLock file cannot be parsed', $e);
                }
            }
            else
            {
                $this->PackageLock = new PackageLock();
            }
        }

        /**
         * Saves the PackageLock to disk
         *
         * @return void
         * @throws AccessDeniedException
         * @throws PackageLockException
         */
        public function save(): void
        {
            // Don't save something that isn't loaded lol
            if($this->PackageLock == null)
                return;

            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Cannot write to PackageLock, insufficient permissions');

            if(file_exists($this->PackageLockPath) && is_writable($this->PackageLockPath))
            {
                try
                {
                    IO::fwrite($this->PackageLockPath, ZiProto::encode($this->PackageLock->toArray(true)));
                }
                catch(IOException $e)
                {
                    throw new PackageLockException('Cannot save the package lock file to disk', $e);
                }
            }

        }

        /**
         * @return PackageLock|null
         */
        public function getPackageLock(): ?PackageLock
        {
            return $this->PackageLock;
        }
    }