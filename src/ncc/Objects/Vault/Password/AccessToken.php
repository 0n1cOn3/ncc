<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Objects\Vault\Password;

    use ncc\Abstracts\AuthenticationType;
    use ncc\Interfaces\PasswordInterface;
    use ncc\Utilities\Functions;

    class AccessToken implements PasswordInterface
    {
        /**
         * The entry's access token
         * 
         * @var string
         */
        public $AccessToken;

        /**
         * Returns an array representation of the object
         * 
         * @param bool $bytecode
         * @return array
         */
        public function toArray(bool $bytecode=false): array
        {
            return [
                ($bytecode ? Functions::cbc('authentication_type') : 'authentication_type') => AuthenticationType::AccessToken,
                ($bytecode ? Functions::cbc('access_token') : 'access_token') => $this->AccessToken,
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
            $object = new self();
            
            $object->AccessToken = Functions::array_bc($data, 'access_token');
            
            return $object;
        }

        /**
         * @return string
         */
        public function getAccessToken(): string
        {
            return $this->AccessToken;
        }

        /**
         * @inheritDoc
         */
        public function getAuthenticationType(): string
        {
            return AuthenticationType::AccessToken;
        }

        /**
         * Returns a string representation of the object
         *
         * @return string
         */
        public function __toString(): string
        {
            return $this->AccessToken;
        }
    }