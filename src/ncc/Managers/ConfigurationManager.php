<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Managers;

    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\IOException;
    use ncc\ThirdParty\Symfony\Yaml\Yaml;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;
    use ncc\Utilities\Resolver;
    use ncc\Utilities\RuntimeCache;

    class ConfigurationManager
    {
        /**
         * The configuration contents parsed
         *
         * @var mixed
         */
        private $Configuration;

        /**
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws InvalidScopeException
         */
        public function __construct()
        {
            $this->load();
        }

        /**
         * Loads the configuration file if it exists
         *
         * @return void
         * @throws AccessDeniedException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws InvalidScopeException
         */
        public function load()
        {
            $this->Configuration = RuntimeCache::get('ncc.yaml');
            if($this->Configuration !== null)
                return;
            $configuration_contents = IO::fread(PathFinder::getConfigurationFile());
            $this->Configuration = Yaml::parse($configuration_contents);
        }

        /**
         * Saves the configuration file to disk
         *
         * @return void
         * @throws AccessDeniedException
         * @throws IOException
         * @throws InvalidScopeException
         */
        public function save()
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Cannot save configuration file, insufficient permissions');

            if($this->Configuration == null)
                return;

            IO::fwrite(PathFinder::getConfigurationFile(), Yaml::dump($this->Configuration));
        }
    }