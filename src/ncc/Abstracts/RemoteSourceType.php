<?php

    namespace ncc\Abstracts;

    abstract class RemoteSourceType
    {
        /**
         * A builtin source type is not defined by the user but handled by
         * an extension built into NCC
         */
        const Builtin = 'builtin';

        /**
         * A defined source type is defined by the user in the remote sources file
         * and handled by an extension designed by passing on the information of
         * the source to the extension
         */
        const Defined = 'defined';

        /**
         * Unsupported or invalid source type
         */
        const Unknown = 'unknown';

        const All = [
            self::Builtin,
            self::Defined,
            self::Unknown
        ];
    }