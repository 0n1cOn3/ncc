<?php

    namespace ncc\Abstracts;

    abstract class Runners
    {
        const php = 'php';

        const bash = 'bash';

        const python = 'python';

        const python3 = 'python3';

        const python2 = 'python2';

        const perl = 'perl';

        const lua = 'lua';


        const All = [
            self::php,
            self::bash,
            self::python,
            self::python3,
            self::python2,
            self::perl,
            self::lua
        ];
    }