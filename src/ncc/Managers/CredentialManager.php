<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Managers;

    use Exception;
    use ncc\Abstracts\Scopes;
    use ncc\Abstracts\Versions;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\RuntimeException;
    use ncc\Objects\Vault;
    use ncc\Utilities\IO;
    use ncc\Utilities\PathFinder;
    use ncc\Utilities\Resolver;
    use ncc\ZiProto\ZiProto;

    class CredentialManager
    {
        /**
         * @var string
         */
        private $CredentialsPath;


        /**
         * @var Vault
         */
        private $Vault;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->CredentialsPath = PathFinder::getDataPath(Scopes::System) . DIRECTORY_SEPARATOR . 'credentials.store';
            $this->Vault = null;

            try
            {
                $this->loadVault();
            }
            catch(Exception $e)
            {
                unset($e);
            }

            if($this->Vault == null)
                $this->Vault = new Vault();
        }

        /**
         * Constructs the store file if it doesn't exist on the system (First initialization)
         *
         * @return void
         * @throws AccessDeniedException
         * @throws IOException
         */
        public function constructStore(): void
        {
            // Do not continue the function if the file already exists, if the file is damaged a separate function
            // is to be executed to fix the damaged file.
            if(file_exists($this->CredentialsPath))
                return;

            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Cannot construct credentials store without system permissions');

            $VaultObject = new Vault();
            $VaultObject->Version = Versions::CredentialsStoreVersion;

            IO::fwrite($this->CredentialsPath, ZiProto::encode($VaultObject->toArray()), 0744);
        }

        /**
         * Loads the vault from the disk
         *
         * @return void
         * @throws AccessDeniedException
         * @throws IOException
         * @throws RuntimeException
         * @throws FileNotFoundException
         */
        private function loadVault(): void
        {
            if($this->Vault !== null)
                return;

            if(!file_exists($this->CredentialsPath))
            {
                $this->Vault = new Vault();
                return;
            }

            $VaultArray = ZiProto::decode(IO::fread($this->CredentialsPath));
            $VaultObject = Vault::fromArray($VaultArray);

            if($VaultObject->Version !== Versions::CredentialsStoreVersion)
                throw new RuntimeException('Credentials store version mismatch');

            $this->Vault = $VaultObject;
        }

        /**
         * Saves the vault to the disk
         *
         * @return void
         * @throws AccessDeniedException
         * @throws IOException
         * @noinspection PhpUnused
         */
        public function saveVault(): void
        {
            if(Resolver::resolveScope() !== Scopes::System)
                throw new AccessDeniedException('Cannot save credentials store without system permissions');

            IO::fwrite($this->CredentialsPath, ZiProto::encode($this->Vault->toArray()), 0744);
        }


        /**
         * @return string
         * @noinspection PhpUnused
         */
        public function getCredentialsPath(): string
        {
            return $this->CredentialsPath;
        }

        /**
         * @return Vault|null
         */
        public function getVault(): ?Vault
        {
            return $this->Vault;
        }
    }