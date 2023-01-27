<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Managers;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\IOException;
    use ncc\Objects\DefinedRemoteSource;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;
    use ncc\ZiProto\ZiProto;

    class RemoteSourcesManager
    {
        /**
         * An array of all the defined remote sources
         *
         * @var DefinedRemoteSource[]
         */
        private $Sources;

        /**
         * The path to the remote sources file
         *
         * @var string
         */
        private $DefinedSourcesPath;

        /**
         * Public Constructor
         *
         * @throws InvalidScopeException
         */
        public function __construct()
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->DefinedSourcesPath = PathFinder::getRemoteSources(Scopes::System);

            $this->load();
        }

        /**
         * Loads an existing remote sources file, or creates a new one if it doesn't exist
         *
         * @return void
         */
        public function load(): void
        {
            $this->Sources = [];

            try
            {

                if(file_exists($this->DefinedSourcesPath))
                {
                    $sources = ZiProto::decode(IO::fread($this->DefinedSourcesPath));
                    $this->Sources = [];
                    foreach($sources as $source)
                        $this->Sources[] = DefinedRemoteSource::fromArray($source);
                }
            }
            catch(Exception $e)
            {
                unset($e);
            }
        }

        /**
         * Saves the remote sources file to disk
         *
         * @return void
         * @throws IOException
         */
        public function save(): void
        {
            $sources = [];
            foreach($this->Sources as $source)
                $sources[] = $source->toArray(true);

            IO::fwrite($this->DefinedSourcesPath, ZiProto::encode($sources));
        }

        /**
         * Adds a new remote source to the list
         *
         * @param DefinedRemoteSource $source
         * @return bool
         */
        public function addRemoteSource(DefinedRemoteSource $source): bool
        {
            foreach($this->Sources as $existingSource)
            {
                if($existingSource->Name === $source->Name)
                    return false;
            }

            $this->Sources[] = $source;
            return true;
        }

        /**
         * Gets a remote source by its name
         *
         * @param string $name
         * @return DefinedRemoteSource|null
         */
        public function getRemoteSource(string $name): ?DefinedRemoteSource
        {
            foreach($this->Sources as $source)
            {
                if($source->Name === $name)
                    return $source;
            }

            return null;
        }

        /**
         * Deletes an existing remote source
         *
         * @param string $name
         * @return bool
         */
        public function deleteRemoteSource(string $name): bool
        {
            foreach($this->Sources as $index => $source)
            {
                if($source->Name === $name)
                {
                    unset($this->Sources[$index]);
                    return true;
                }
            }

            return false;
        }

        /**
         * Returns an array of all the defined remote sources
         *
         * @return DefinedRemoteSource[]
         */
        public function getSources(): array
        {
            if($this->Sources == null)
                $this->load();
            return $this->Sources;
        }
    }