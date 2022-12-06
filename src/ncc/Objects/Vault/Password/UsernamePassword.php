<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\Vault\Password;

    use ncc\Abstracts\AuthenticationType;
    use ncc\Interfaces\PasswordInterface;
    use ncc\Utilities\Functions;

    class UsernamePassword implements PasswordInterface
    {
        /**
         * The entry's username
         *
         * @var string
         */
        public $Username;

        /**
         * The entry's password
         *
         * @var string
         */
        public $Password;

        /**
         * Returns an array representation of the object
         *
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('authentication_type') : 'authentication_type') => AuthenticationType::UsernamePassword,
                ($bytecode ? Functions::cbc('username') : 'username') => $this->Username,
                ($bytecode ? Functions::cbc('password') : 'password') => $this->Password,
            ];
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return static
         */
        public static function fromArray(array $data): self
        {
            $instance = new self();

            $instance->Username = Functions::array_bc($data, 'username');
            $instance->Password = Functions::array_bc($data, 'password');

            return $instance;
        }

        /**
         * @return string
         * @noinspection PhpUnused
         */
        public function getUsername(): string
        {
            return $this->Username;
        }

        /**
         * @return string
         * @noinspection PhpUnused
         */
        public function getPassword(): string
        {
            return $this->Password;
        }

        /**
         * @inheritDoc
         */
        public function getAuthenticationType(): string
        {
            return AuthenticationType::UsernamePassword;
        }

        /**
         * Returns a string representation of the object
         *
         * @return string
         */
        public function __toString(): string
        {
            return $this->Password;
        }
    }