<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects;

    use ncc\Abstracts\AuthenticationType;
    use ncc\Abstracts\Versions;
    use ncc\Exceptions\RuntimeException;
    use ncc\Interfaces\PasswordInterface;
    use ncc\Objects\Vault\Entry;
    use ncc\Utilities\Functions;

    class Vault
    {
        /**
         * The vault's current version for backwards compatibility
         *
         * @var string
         */
        public $Version;

        /**
         * The vault's stored credential entries
         *
         * @var Entry[]
         */
        public $Entries;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->Version = Versions::CredentialsStoreVersion;
            $this->Entries = [];
        }

        /**
         * Adds a new entry to the vault
         *
         * @param string $name
         * @param PasswordInterface $password
         * @param bool $encrypt
         * @return bool
         * @noinspection PhpUnused
         */
        public function addEntry(string $name, PasswordInterface $password, bool $encrypt=true): bool
        {
            // Check if the entry already exists
            foreach($this->Entries as $entry)
            {
                if($entry->getName() === $name)
                    return false;
            }

            // Create the new entry
            $entry = new Entry();
            $entry->setName($name);
            $entry->setEncrypted($encrypt);
            $entry->setAuthentication($password);

            // Add the entry to the vault
            $this->Entries[] = $entry;
            return true;
        }

        /**
         * Deletes an entry from the vault
         *
         * @param string $name
         * @return bool
         * @noinspection PhpUnused
         */
        public function deleteEntry(string $name): bool
        {
            foreach($this->Entries as $entry)
            {
                if($entry->getName() === $name)
                {
                    $this->Entries = array_diff($this->Entries, [$entry]);
                    return true;
                }
            }

            return false;
        }

        /**
         * Returns all the entries in the vault
         *
         * @return array|Entry[]
         * @noinspection PhpUnused
         */
        public function getEntries(): array
        {
            return $this->Entries;
        }

        /**
         * Returns an existing entry from the vault
         *
         * @param string $name
         * @return Entry|null
         */
        public function getEntry(string $name): ?Entry
        {
            foreach($this->Entries as $entry)
            {
                if($entry->getName() === $name)
                    return $entry;
            }

            return null;
        }

        /**
         * Authenticates an entry in the vault
         *
         * @param string $name
         * @param string $password
         * @return bool
         * @throws RuntimeException
         * @noinspection PhpUnused
         */
        public function authenticate(string $name, string $password): bool
        {
            $entry = $this->getEntry($name);
            if($entry === null)
                return false;

            if($entry->getPassword() === null)
            {
                if($entry->isEncrypted() && !$entry->isCurrentlyDecrypted())
                {
                    return $entry->unlock($password);
                }
            }

            $input = [];
            switch($entry->getPassword()->getAuthenticationType())
            {
                case AuthenticationType::UsernamePassword:
                    $input = ['password' => $password];
                    break;
                case AuthenticationType::AccessToken:
                    $input = ['token' => $password];
                    break;
            }

            return $entry->authenticate($input);
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            $entries = [];
            foreach($this->Entries as $entry)
            {
                $entries[] = $entry->toArray($bytecode);
            }

            return [
                ($bytecode ? Functions::cbc('version') : 'version') => $this->Version,
                ($bytecode ? Functions::cbc('entries') : 'entries') => $entries,
            ];
        }

        /**
         * Constructs a new object from an array
         *
         * @param array $array
         * @return Vault
         */
        public static function fromArray(array $array): Vault
        {
            $vault = new Vault();
            $vault->Version = Functions::array_bc($array, 'version');
            $entries = Functions::array_bc($array, 'entries');
            $vault->Entries = [];

            foreach($entries as $entry)
                $vault->Entries[] = Entry::fromArray($entry);

            return $vault;
        }

    }