<?php

    use ncc\Classes\ComposerExtension\ComposerInstance;
    use ncc\ncc;

    require('ncc');

    ncc::initialize();
    define('NCC_CLI_MODE', 1);

    print(ComposerInstance::require('symfony', 'console', '^3.4') . PHP_EOL);
    print(ComposerInstance::require('laravel', 'laravel', '*') . PHP_EOL);