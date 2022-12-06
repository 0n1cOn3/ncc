<?php

    namespace ncc\Abstracts;

    abstract class AuthenticationType
    {
        /**
         * A combination of a username and password is used for authentication
         */
        const UsernamePassword = 1;

        /**
         * A single private access token is used for authentication
         */
        const AccessToken = 2;
    }