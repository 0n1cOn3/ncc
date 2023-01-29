<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Managers;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\SymlinkException;
    use ncc\Objects\SymlinkDictionary\SymlinkEntry;
    use ncc\ThirdParty\Symfony\Filesystem\Filesystem;
    use ncc\Utilities\Console;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;
    use ncc\Utilities\Resolver;
    use ncc\ZiProto\ZiProto;

    class SymlinkManager
    {
        /**
         * @var string
         */
        private static $BinPath = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'bin';

        /**
         * The path to the symlink dictionary file
         *
         * @var string
         */
        private $SymlinkDictionaryPath;

        /**
         * An array of all the defined symlinks
         *
         * @var SymlinkEntry[]
         */
        private $SymlinkDictionary;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            try
            {
                $this->SymlinkDictionaryPath = PathFinder::getSymlinkDictionary(Scopes::System);
                $this->load();
            }
            catch(Exception $e)
            {
                Console::outWarning(sprintf('failed to load symlink dictionary from %s', $this->SymlinkDictionaryPath));
            }
            finally
            {
                if($this->SymlinkDictionary === null)
                    $this->SymlinkDictionary = [];

                unset($e);
            }
        }

        /**
         * Loads the symlink dictionary from the file
         *
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        public function load(): void
        {
            if($this->SymlinkDictionary !== null)
                return;

            Console::outDebug(sprintf('loading symlink dictionary from %s', $this->SymlinkDictionaryPath));

            if(!file_exists($this->SymlinkDictionaryPath))
            {
                Console::outDebug('symlink dictionary does not exist, creating new dictionary');
                $this->SymlinkDictionary = [];
                $this->save(false);
                return;
            }

            try
            {
                $this->SymlinkDictionary = [];

                foreach(ZiProto::decode(IO::fread($this->SymlinkDictionaryPath)) as $entry)
                {
                    $this->SymlinkDictionary[] = SymlinkEntry::fromArray($entry);
                }
            }
            catch(Exception $e)
            {
                $this->SymlinkDictionary = [];

                Console::outDebug('symlink dictionary is corrupted, creating new dictionary');
                $this->save(false);
            }
            finally
            {
                unset($e);
            }
        }

        /**
         * Saves the symlink dictionary to the file
         *
         * @param bool $throw_exception
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        private function save(bool $throw_exception=true): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Insufficient Permissions to write to the system symlink dictionary');

            Console::outDebug(sprintf('saving symlink dictionary to %s', $this->SymlinkDictionaryPath));

            try
            {
                $dictionary = [];
                foreach($this->SymlinkDictionary as $entry)
                {
                    $dictionary[] = $entry->toArray(true);
                }

                IO::fwrite($this->SymlinkDictionaryPath, ZiProto::encode($dictionary));
            }
            catch(Exception $e)
            {
                if($throw_exception)
                    throw new SymlinkException(sprintf('failed to save symlink dictionary to %s', $this->SymlinkDictionaryPath), $e);

                Console::outWarning(sprintf('failed to save symlink dictionary to %s', $this->SymlinkDictionaryPath));
            }
            finally
            {
                unset($e);
            }
        }

        /**
         * @return string
         */
        public function getSymlinkDictionaryPath(): string
        {
            return $this->SymlinkDictionaryPath;
        }

        /**
         * @return array
         */
        public function getSymlinkDictionary(): array
        {
            return $this->SymlinkDictionary;
        }

        /**
         * Checks if a package is defined in the symlink dictionary
         *
         * @param string $package
         * @return bool
         */
        public function exists(string $package): bool
        {
            foreach($this->SymlinkDictionary as $entry)
            {
                if($entry->Package === $package)
                    return true;
            }

            return false;
        }

        /**
         * Adds a new entry to the symlink dictionary
         *
         * @param string $package
         * @param string $unit
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        public function add(string $package, string $unit='main'): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Insufficient Permissions to add to the system symlink dictionary');

            if($this->exists($package))
                $this->remove($package);

            $entry = new SymlinkEntry();
            $entry->Package = $package;
            $entry->ExecutionPolicyName = $unit;

            $this->SymlinkDictionary[] = $entry;
            $this->save();
        }

        /**
         * Removes an entry from the symlink dictionary
         *
         * @param string $package
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        public function remove(string $package): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Insufficient Permissions to remove from the system symlink dictionary');

            if(!$this->exists($package))
                return;

            foreach($this->SymlinkDictionary as $key => $entry)
            {
                if($entry->Package === $package)
                {
                    if($entry->Registered)
                    {
                        $filesystem = new Filesystem();

                        $symlink_name = explode('.', $entry->Package)[count(explode('.', $entry->Package)) - 1];
                        $symlink = self::$BinPath . DIRECTORY_SEPARATOR . $symlink_name;

                        if($filesystem->exists($symlink))
                            $filesystem->remove($symlink);
                    }

                    unset($this->SymlinkDictionary[$key]);
                    $this->save();
                    return;
                }
            }

            throw new SymlinkException(sprintf('failed to remove package %s from the symlink dictionary', $package));
        }

        /**
         * Sets the package as registered
         *
         * @param string $package
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        private function setAsRegistered(string $package): void
        {
            foreach($this->SymlinkDictionary as $key => $entry)
            {
                if($entry->Package === $package)
                {
                    $entry->Registered = true;
                    $this->SymlinkDictionary[$key] = $entry;
                    $this->save();
                    return;
                }
            }
        }

        /**
         * Sets the package as unregistered
         *
         * @param string $package
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        private function setAsUnregistered(string $package): void
        {
            foreach($this->SymlinkDictionary as $key => $entry)
            {
                if($entry->Package === $package)
                {
                    $entry->Registered = false;
                    $this->SymlinkDictionary[$key] = $entry;
                    $this->save();
                    return;
                }
            }
        }

        /**
         * Syncs the symlink dictionary with the filesystem
         *
         * @return void
         * @throws AccessDeniedException
         * @throws SymlinkException
         */
        public function sync(): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Insufficient Permissions to sync the system symlink dictionary');

            $filesystem = new Filesystem();
            $execution_pointer_manager = new ExecutionPointerManager();
            $package_lock_manager = new PackageLockManager();

            foreach($this->SymlinkDictionary as $entry)
            {
                if($entry->Registered)
                    continue;

                $symlink_name = explode('.', $entry->Package)[count(explode('.', $entry->Package)) - 1];
                $symlink = self::$BinPath . DIRECTORY_SEPARATOR . $symlink_name;

                if($filesystem->exists($symlink))
                {
                    Console::outWarning(sprintf('Symlink %s already exists, skipping', $symlink));
                    continue;
                }

                try
                {
                    $package_entry = $package_lock_manager->getPackageLock()->getPackage($entry->Package);

                    if($package_entry == null)
                    {
                        Console::outWarning(sprintf('Package %s is not installed, skipping', $entry->Package));
                        continue;
                    }

                    $latest_version = $package_entry->getLatestVersion();

                }
                catch(Exception $e)
                {
                    $filesystem->remove($symlink);
                    Console::outWarning(sprintf('Failed to get package %s, skipping', $entry->Package));
                    continue;
                }

                try
                {
                    $entry_point_path = $execution_pointer_manager->getEntryPointPath($entry->Package, $latest_version, $entry->ExecutionPolicyName);
                    $filesystem->symlink($entry_point_path, $symlink);
                }
                catch(Exception $e)
                {
                    $filesystem->remove($symlink);
                    Console::outWarning(sprintf('Failed to create symlink %s, skipping', $symlink));
                    continue;
                }
                finally
                {
                    unset($e);
                }

                $this->setAsRegistered($entry->Package);

            }
        }
    }