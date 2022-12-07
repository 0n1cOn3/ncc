<?php

    namespace ncc\CLI;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Managers\CredentialManager;
    use ncc\Objects\CliHelpSection;
    use ncc\Objects\Vault\Password\AccessToken;
    use ncc\Objects\Vault\Password\UsernamePassword;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;
    use ncc\Utilities\Resolver;

    class CredentialMenu
    {
        /**
         * Displays the main help menu
         *
         * @param $args
         * @return void
         * @noinspection PhpNoReturnAttributeCanBeAddedInspection
         */
        public static function start($args): void
        {
            if(isset($args['add']))
            {
                self::addCredential($args);
            }

            self::displayOptions();
            exit(0);
        }

        /**
         * @param $args
         * @return void
         */
        public static function addCredential($args): void
        {
            $ResolvedScope = Resolver::resolveScope();

            if($ResolvedScope !== Scopes::System)
                Console::outError('Insufficient permissions to add credentials');

            // Really dumb-proofing this
            $name = $args['alias'] ?? $args['name'] ?? null;
            $auth_type = $args['auth-type'] ?? $args['auth'] ?? null;
            $username = $args['username'] ?? $args['usr'] ?? null;
            $password = $args['password'] ?? $args['pwd'] ?? null;
            $token = $args['token'] ?? $args['pat'] ?? $args['private-token'] ?? null;
            $encrypt = $args['encrypt'] ?? $args['encrypted'] ?? null;

            if($name === null)
                $name = Console::getInput('Enter a name for the entry: ');

            if($auth_type === null)
                $auth_type = Console::getInput('Enter the authentication type (login or pat): ');

            if($auth_type === 'login')
            {
                if($username === null)
                    $username = Console::getInput('Username: ');

                if($password === null)
                    $password = Console::passwordInput('Password: ');
            }
            elseif($auth_type === 'pat')
            {
                if($token === null)
                    $token = Console::passwordInput('Token: ');
            }
            else
            {
                Console::outError('Invalid authentication type');
            }

            if($encrypt === null)
                $encrypt = Console::getBooleanInput('Encrypt entry with your password?');

            if($name === null)
            {
                Console::outError('You must specify a name for the entry (alias, name)', true, 1);
                return;
            }

            if($auth_type === null)
            {
                Console::outError('You must specify an authentication type for the entry (auth-type, auth)', true, 1);
                return;
            }

            $encrypt = Functions::cbool($encrypt);

            switch($auth_type)
            {
                case 'login':

                    if($username === null)
                    {
                        Console::outError('You must specify a username for the entry (username, usr)', true, 1);
                        return;
                    }
                    if($password === null)
                    {
                        Console::outError('You must specify a password for the entry (password, pwd)', true, 1);
                        return;
                    }

                    $pass_object = new UsernamePassword();
                    $pass_object->setUsername($username);
                    $pass_object->setPassword($password);

                    break;

                case 'pat':

                    if($token === null)
                    {
                        Console::outError('You must specify a token for the entry (token, pat, private-token)', true, 1);
                        return;
                    }

                    $pass_object = new AccessToken();
                    $pass_object->setAccessToken($token);

                    break;

                default:
                    Console::outError('Invalid authentication type specified', true, 1);
                    return;
            }

            $credential_manager = new CredentialManager();
            if(!$credential_manager->getVault()->addEntry($name, $pass_object, $encrypt))
            {
                Console::outError('Failed to add entry, entry already exists.', true, 1);
                return;
            }

            try
            {
                $credential_manager->saveVault();
            }
            catch(Exception $e)
            {
                Console::outException('Failed to save vault', $e, 1);
                return;
            }

            Console::out('Successfully added entry', true, 0);
            exit(0);
        }

        /**
         * Displays the main options section
         *
         * @return void
         */
        private static function displayOptions(): void
        {
            Console::out('Usage: ncc vault {command} [options]');
            Console::out('Options:');
            Console::outHelpSections([
                new CliHelpSection(['help'], 'Displays this help menu about the value command'),
                new CliHelpSection(['add'], 'Adds a new entry to the vault (See below)'),
                new CliHelpSection(['remove', '--name'], 'Removes'),
            ]);
            Console::out((string)null);

            Console::out('If you are adding a new entry, you can run the add command in interactive mode');
            Console::out('or you can specify the options below' . PHP_EOL);

            Console::out('Add Options:');
            Console::outHelpSections([
                new CliHelpSection(['--name'], 'The name of the entry'),
                new CliHelpSection(['--auth-type', '--auth'], 'The type of authentication (login, pat)'),
                new CliHelpSection(['--encrypted', '--encrypt'], 'Whether or not to encrypt the entry', true),
            ]);

            Console::out('   login authentication type options:');
            Console::outHelpSections([
                new CliHelpSection(['--username', '--usr'], 'The username for the entry'),
                new CliHelpSection(['--password', '--pwd'], 'The password for the entry'),
            ]);

            Console::out('   pat authentication type options:');
            Console::outHelpSections([
                new CliHelpSection(['--token', '--pat',], 'The private access token for the entry', true),
            ]);

            Console::out('Authentication Types:');
            Console::out('   login');
            Console::out('   pat' . PHP_EOL);

            Console::out('Examples:');
            Console::out('   ncc vault add --alias "My Alias" --auth-type login --username "myusername" --password "mypassword" --encrypt');
            Console::out('   ncc vault add --alias "My Alias" --auth-type pat --token "mytoken"');
            Console::out('   ncc vault remove --alias "My Alias"');
        }
    }