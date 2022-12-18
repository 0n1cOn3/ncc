<?php

    namespace ncc\CLI\Management;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Exceptions\IOException;
    use ncc\Managers\RemoteSourcesManager;
    use ncc\Objects\CliHelpSection;
    use ncc\Objects\DefinedRemoteSource;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;
    use ncc\Utilities\Resolver;

    class SourcesMenu
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         */
        public static function start($args): void
        {
            if(isset($args['add']))
            {
                try
                {
                    self::addEntry($args);
                }
                catch(Exception $e)
                {
                    Console::outException('Error while adding entry.', $e, 1);
                }

                return;
            }

            if(isset($args['remove']))
            {
                try
                {
                    self::removeEntry($args);
                }
                catch(Exception $e)
                {
                    Console::outException('Cannot remove entry.', $e, 1);
                }

                return;
            }

            if(isset($args['list']))
            {
                try
                {
                    self::listEntries();
                }
                catch(Exception $e)
                {
                    Console::outException('Cannot list entries.', $e, 1);
                }

                return;
            }

            self::displayOptions();
        }


        /**
         * @return void
         */
        public static function listEntries(): void
        {
            $source_manager = new RemoteSourcesManager();
            $sources = $source_manager->getSources();

            if(count($sources) == 0)
            {
                Console::out('No remote sources defined.', 1);
                return;
            }

            Console::out('Remote sources:', 1);
            foreach($sources as $source)
            {
                Console::out(' - ' . $source->Name . ' (' . $source->Host . ')', 1);
            }

            Console::out('Total: ' . count($sources), 1);
        }

        /**
         * @param $args
         * @return void
         */
        public static function addEntry($args): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
            {
                Console::outError('Insufficient permissions to add entry.', true, 1);
                return;
            }

            $name = $args['name'] ?? null;
            $type = $args['type'] ?? null;
            $host = $args['host'] ?? null;
            $ssl = $args['ssl'] ?? null;

            if($name == null)
            {
                Console::outError(sprintf('Missing required argument \'%s\'.', 'name'), true, 1);
                return;
            }

            if($type == null)
            {
                Console::outError(sprintf('Missing required argument \'%s\'.', 'type'), true, 1);
                return;
            }

            if($host == null)
            {
                Console::outError(sprintf('Missing required argument \'%s\'.', 'host'), true, 1);
                return;
            }

            if($ssl !== null)
            {
                $ssl = Functions::cbool($ssl);
            }

            $source_manager = new RemoteSourcesManager();
            $source = new DefinedRemoteSource();
            $source->Name = $name;
            $source->Type = $type;
            $source->Host = $host;
            $source->SSL = $ssl;

            if(!$source_manager->addRemoteSource($source))
            {
                Console::outError(sprintf('Cannot add entry \'%s\', it already exists', $name), true, 1);
                return;
            }

            try
            {
                $source_manager->save();
            }
            catch (IOException $e)
            {
                Console::outException('Cannot save remote sources file.', $e, 1);
                return;
            }

            Console::out(sprintf('Entry \'%s\' added successfully.', $name));
        }

        /**
         * Removes an existing entry from the vault.
         *
         * @param $args
         * @return void
         */
        private static function removeEntry($args): void
        {
            $ResolvedScope = Resolver::resolveScope();

            if($ResolvedScope !== Scopes::System)
                Console::outError('Insufficient permissions to remove entries');

            $name = $args['name'] ?? null;

            if($name == null)
            {
                Console::outError(sprintf('Missing required argument \'%s\'.', 'name'), true, 1);
                return;
            }

            $source_manager = new RemoteSourcesManager();

            if(!$source_manager->deleteRemoteSource($name))
            {
                Console::outError(sprintf('Cannot remove entry \'%s\', it does not exist', $name), true, 1);
                return;
            }

            try
            {
                $source_manager->save();
            }
            catch (IOException $e)
            {
                Console::outException('Cannot save remote sources file.', $e, 1);
                return;

            }
            Console::out(sprintf('Entry \'%s\' removed successfully.', $name));
        }

        /**
         * Displays the main options section
         *
         * @return void
         */
        private static function displayOptions(): void
        {
            Console::out('Usage: ncc sources {command} [options]');
            Console::out('Options:');
            Console::outHelpSections([
                new CliHelpSection(['help'], 'Displays this help menu about the sources command'),
                new CliHelpSection(['add'], 'Adds a new entry to the list of remote sources (See below)'),
                new CliHelpSection(['remove', '--name'], 'Removes an entry from the list'),
                new CliHelpSection(['list'], 'Lists all entries defined as remote sources'),
            ]);
            Console::out((string)null);

        }
    }