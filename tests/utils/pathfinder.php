<?php

    require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php');

    print('Unix Root Directory: ' . \ncc\Utilities\PathFinder::getRootPath(false) . PHP_EOL);
    print('Win32 Root Directory: ' . \ncc\Utilities\PathFinder::getRootPath(true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Home Directory (Auto): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Home Directory (User): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Home Directory (System): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Home Directory (Auto): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Home Directory (User): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Home Directory (System): ' . \ncc\Utilities\PathFinder::getHomePath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Data Directory (Auto): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Data Directory (User): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Data Directory (System): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Data Directory (Auto): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Data Directory (User): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Data Directory (System): ' . \ncc\Utilities\PathFinder::getDataPath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Packages Directory (Auto): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Packages Directory (User): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Packages Directory (System): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Packages Directory (Auto): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Packages Directory (User): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Packages Directory (System): ' . \ncc\Utilities\PathFinder::getPackagesPath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Cache Directory (Auto): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Cache Directory (User): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Cache Directory (System): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Cache Directory (Auto): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Cache Directory (User): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Cache Directory (System): ' . \ncc\Utilities\PathFinder::getCachePath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Tmp Directory (Auto): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Tmp Directory (User): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Tmp Directory (System): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Tmp Directory (Auto): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Tmp Directory (User): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Tmp Directory (System): ' . \ncc\Utilities\PathFinder::getTmpPath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Extension Directory (Auto): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Extension Directory (User): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Extension Directory (System): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Win32 Extension Directory (Auto): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Extension Directory (User): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Extension Directory (System): ' . \ncc\Utilities\PathFinder::getExtensionPath(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Configuration File (Auto): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Configuration File (User): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Configuration File (System): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Unix Configuration File(s): ' . json_encode(\ncc\Utilities\PathFinder::getConfigurationFiles(false), JSON_UNESCAPED_SLASHES) . PHP_EOL);
    print('Win32 Configuration File (Auto): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Configuration File (User): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Configuration File (System): ' . \ncc\Utilities\PathFinder::getConfigurationFile(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print('Win32 Configuration File(s): ' . json_encode(\ncc\Utilities\PathFinder::getConfigurationFiles(true), JSON_UNESCAPED_SLASHES) . PHP_EOL);
    print(PHP_EOL);

    print('Unix Package Lock (Auto): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::AUTO, false) . PHP_EOL);
    print('Unix Package Lock (User): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::USER, false) . PHP_EOL);
    print('Unix Package Lock (System): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::SYSTEM, false) . PHP_EOL);
    print('Unix Package Lock(s): ' . json_encode(\ncc\Utilities\PathFinder::getPackageLockFiles(false), JSON_UNESCAPED_SLASHES) . PHP_EOL);
    print('Win32 Package Lock (Auto): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::AUTO, true) . PHP_EOL);
    print('Win32 Package Lock (User): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::USER, true) . PHP_EOL);
    print('Win32 Package Lock (System): ' . \ncc\Utilities\PathFinder::getPackageLock(\ncc\Enums\Scopes::SYSTEM, true) . PHP_EOL);
    print('Win32 Package Lock(s): ' . json_encode(\ncc\Utilities\PathFinder::getPackageLockFiles(true), JSON_UNESCAPED_SLASHES) . PHP_EOL);
    print(PHP_EOL);