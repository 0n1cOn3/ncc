<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\Vault;

    use ncc\Abstracts\AuthenticationType;
    use ncc\Defuse\Crypto\Crypto;
    use ncc\Defuse\Crypto\Exception\EnvironmentIsBrokenException;
    use ncc\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
    use ncc\Exceptions\RuntimeException;
    use ncc\Interfaces\PasswordInterface;
    use ncc\Objects\Vault\Password\AccessToken;
    use ncc\Objects\Vault\Password\UsernamePassword;
    use ncc\Utilities\Functions;
    use ncc\ZiProto\ZiProto;

    class Entry
    {
        /**
         * The entry's unique identifier
         *
         * @var string
         */
        private $Name;

        /**
         * Whether the entry's password is encrypted
         *
         * @var bool
         */
        private $Encrypted;

        /**
         * The entry's password
         *
         * @var PasswordInterface|string|null
         */
        private $Password;

        /**
         * Whether the entry's password is currently decrypted in memory
         * (Not serialized)
         *
         * @var bool
         */
        private $IsCurrentlyDecrypted;

        /**
         * Returns an array representation of the object
         *
         */
        public function __construct()
        {
            $this->Encrypted = true;
            $this->IsCurrentlyDecrypted = true;
        }

        /**
         * Test Authenticates the entry
         *
         * For UsernamePassword the $input parameter expects an array with the keys 'username' and 'password'
         * For AccessToken the $input parameter expects an array with the key 'token'
         *
         * @param array $input
         * @return bool
         * @noinspection PhpUnused
         */
        public function authenticate(array $input): bool
        {
            if(!$this->IsCurrentlyDecrypted)
                return false;

            if($this->Password == null)
                return false;

            switch($this->Password->getAuthenticationType())
            {
                case AuthenticationType::UsernamePassword:
                    if(!($this->Password instanceof UsernamePassword))
                        return false;

                    $username = $input['username'] ?? null;
                    $password = $input['password'] ?? null;

                    if($username === null && $password === null)
                        return false;

                    if($username == null)
                        return $password == $this->Password->getPassword();

                    if($password == null)
                        return $username == $this->Password->getUsername();

                    return $username == $this->Password->getUsername() && $password == $this->Password->getPassword();

                case AuthenticationType::AccessToken:
                    if(!($this->Password instanceof AccessToken))
                        return false;

                    $token = $input['token'] ?? null;

                    if($token === null)
                        return false;

                    return $token == $this->Password->AccessToken;

                default:
                    return false;

            }
        }

        /**
         * @param PasswordInterface $password
         * @return void
         */
        public function setAuthentication(PasswordInterface $password): void
        {
            $this->Password = $password;
        }

        /**
         * @return bool
         * @noinspection PhpUnused
         */
        public function isCurrentlyDecrypted(): bool
        {
            return $this->IsCurrentlyDecrypted;
        }

        /**
         * Locks the entry by encrypting the password
         *
         * @return bool
         */
        public function lock(): bool
        {
            if($this->Password == null)
                return false;

            if($this->Encrypted)
                return false;

            if(!$this->IsCurrentlyDecrypted)
                return false;

            if(!($this->Password instanceof PasswordInterface))
                return false;

            $this->Password = $this->encrypt();
            return true;
        }

        /**
         * Unlocks the entry by decrypting the password
         *
         * @param string $password
         * @return bool
         * @throws RuntimeException
         * @noinspection PhpUnused
         */
        public function unlock(string $password): bool
        {
            if($this->Password == null)
                return false;

            if(!$this->Encrypted)
                return false;

            if($this->IsCurrentlyDecrypted)
                return false;

            if(!is_string($this->Password))
                return false;

            try
            {
                $password = Crypto::decryptWithPassword($this->Password, $password, true);
            }
            catch (EnvironmentIsBrokenException $e)
            {
                throw new RuntimeException('Cannot decrypt password', $e);
            }
            catch (WrongKeyOrModifiedCiphertextException $e)
            {
                unset($e);
                return false;
            }

            $this->Password = ZiProto::decode($password);
            $this->IsCurrentlyDecrypted = true;

            return true;
        }

        /**
         * Returns the password object as an encrypted binary string
         *
         * @return string|null
         */
        private function encrypt(): ?string
        {
            if(!$this->IsCurrentlyDecrypted)
                return false;

            if($this->Password == null)
                return false;

            if(!($this->Password instanceof PasswordInterface))
                return null;

            $password = ZiProto::encode($this->Password->toArray(true));
            return Crypto::encryptWithPassword($password, $password, true);
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            if($this->Password !== null)
            {
                if($this->Encrypted && $this->IsCurrentlyDecrypted)
                {
                    $password = $this->encrypt();
                }
                elseif($this->Encrypted)
                {
                    $password = $this->Password;
                }
                else
                {
                    $password = $this->Password->toArray(true);
                }
            }
            else
            {
                $password = $this->Password;
            }

            return [
                ($bytecode ? Functions::cbc('name') : 'name') => $this->Name,
                ($bytecode ? Functions::cbc('encrypted') : 'encrypted') => $this->Encrypted,
                ($bytecode ? Functions::cbc('password') : 'password') => $password,
            ];
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return Entry
         */
        public static function fromArray(array $data): self
        {
            $self = new self();

            $self->Name = Functions::array_bc($data, 'name');
            $self->Encrypted = Functions::array_bc($data, 'encrypted');
            $password = Functions::array_bc($data, 'password');

            if($password !== null)
            {
                if($self->Encrypted)
                {
                    $self->Password = $password;
                    $self->IsCurrentlyDecrypted = false;
                }
                elseif(gettype($password) == 'array')
                {
                    $self->Password = match (Functions::array_bc($password, 'authentication_type'))
                    {
                        AuthenticationType::UsernamePassword => UsernamePassword::fromArray($password),
                        AuthenticationType::AccessToken => AccessToken::fromArray($password)
                    };
                }
            }

            return $self;
        }

        /**
         * @return bool
         */
        public function isEncrypted(): bool
        {
            return $this->Encrypted;
        }

        /**
         * Returns false if the entry needs to be decrypted first
         *
         * @param bool $Encrypted
         * @return bool
         */
        public function setEncrypted(bool $Encrypted): bool
        {
            if(!$this->IsCurrentlyDecrypted)
                return false;

            $this->Encrypted = $Encrypted;
            return true;
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->Name;
        }

        /**
         * @param string $Name
         */
        public function setName(string $Name): void
        {
            $this->Name = $Name;
        }

        /**
         * @return PasswordInterface|null
         */
        public function getPassword(): ?PasswordInterface
        {
            if(!$this->IsCurrentlyDecrypted)
                return null;

            return $this->Password;
        }
    }