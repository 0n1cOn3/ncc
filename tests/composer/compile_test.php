<?php

    use ncc\Classes\ComposerExtension\ComposerCompatibility;
    use ncc\Classes\ComposerExtension\ComposerInstance;
    use ncc\ncc;

    require('ncc');

    ncc::initialize();
    define('NCC_CLI_MODE', 1);

    $require = ComposerInstance::require('laravel', 'laravel', '*');
    ComposerCompatibility::compilePackages($require . DIRECTORY_SEPARATOR . 'composer.lock');
