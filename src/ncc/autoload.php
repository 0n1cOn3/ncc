<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'ncc\\abstracts\\authenticationsource' => '/Abstracts/AuthenticationSource.php',
                'ncc\\abstracts\\exceptioncodes' => '/Abstracts/ExceptionCodes.php',
                'ncc\\abstracts\\nccbuildflags' => '/Abstracts/NccBuildFlags.php',
                'ncc\\abstracts\\regexpatterns' => '/Abstracts/RegexPatterns.php',
                'ncc\\abstracts\\remoteauthenticationtype' => '/Abstracts/RemoteAuthenticationType.php',
                'ncc\\abstracts\\remotesource' => '/Abstracts/RemoteSource.php',
                'ncc\\abstracts\\scopes' => '/Abstracts/Scopes.php',
                'ncc\\abstracts\\stringpaddingmethod' => '/Abstracts/StringPaddingMethod.php',
                'ncc\\abstracts\\versions' => '/Abstracts/Versions.php',
                'ncc\\cli\\credentialmenu' => '/CLI/CredentialMenu.php',
                'ncc\\cli\\functions' => '/CLI/Functions.php',
                'ncc\\cli\\helpmenu' => '/CLI/HelpMenu.php',
                'ncc\\cli\\main' => '/CLI/Main.php',
                'ncc\\defuse\\crypto\\core' => '/ThirdParty/defuse/php-encryption/Core.php',
                'ncc\\defuse\\crypto\\crypto' => '/ThirdParty/defuse/php-encryption/Crypto.php',
                'ncc\\defuse\\crypto\\derivedkeys' => '/ThirdParty/defuse/php-encryption/DerivedKeys.php',
                'ncc\\defuse\\crypto\\encoding' => '/ThirdParty/defuse/php-encryption/Encoding.php',
                'ncc\\defuse\\crypto\\exception\\badformatexception' => '/ThirdParty/defuse/php-encryption/Exception/BadFormatException.php',
                'ncc\\defuse\\crypto\\exception\\cryptoexception' => '/ThirdParty/defuse/php-encryption/Exception/CryptoException.php',
                'ncc\\defuse\\crypto\\exception\\environmentisbrokenexception' => '/ThirdParty/defuse/php-encryption/Exception/EnvironmentIsBrokenException.php',
                'ncc\\defuse\\crypto\\exception\\ioexception' => '/ThirdParty/defuse/php-encryption/Exception/IOException.php',
                'ncc\\defuse\\crypto\\exception\\wrongkeyormodifiedciphertextexception' => '/ThirdParty/defuse/php-encryption/Exception/WrongKeyOrModifiedCiphertextException.php',
                'ncc\\defuse\\crypto\\file' => '/ThirdParty/defuse/php-encryption/File.php',
                'ncc\\defuse\\crypto\\key' => '/ThirdParty/defuse/php-encryption/Key.php',
                'ncc\\defuse\\crypto\\keyorpassword' => '/ThirdParty/defuse/php-encryption/KeyOrPassword.php',
                'ncc\\defuse\\crypto\\keyprotectedbypassword' => '/ThirdParty/defuse/php-encryption/KeyProtectedByPassword.php',
                'ncc\\defuse\\crypto\\runtimetests' => '/ThirdParty/defuse/php-encryption/RuntimeTests.php',
                'ncc\\exceptions\\accessdeniedexception' => '/Exceptions/AccessDeniedException.php',
                'ncc\\exceptions\\directorynotfoundexception' => '/Exceptions/DirectoryNotFoundException.php',
                'ncc\\exceptions\\filenotfoundexception' => '/Exceptions/FileNotFoundException.php',
                'ncc\\exceptions\\invalidcredentialsentryexception' => '/Exceptions/InvalidCredentialsEntryException.php',
                'ncc\\exceptions\\invalidprojectconfigurationexception' => '/Exceptions/InvalidProjectConfigurationException.php',
                'ncc\\exceptions\\invalidscopeexception' => '/Exceptions/InvalidScopeException.php',
                'ncc\\exceptions\\malformedjsonexception' => '/Exceptions/MalformedJsonException.php',
                'ncc\\exceptions\\runtimeexception' => '/Exceptions/RuntimeException.php',
                'ncc\\managers\\credentialmanager' => '/Managers/CredentialManager.php',
                'ncc\\ncc' => '/ncc.php',
                'ncc\\ncc\\ziproto\\typetransformer\\binarytransformer' => '/Extensions/ZiProto/TypeTransformer/BinaryTransformer.php',
                'ncc\\ncc\\ziproto\\typetransformer\\extension' => '/Extensions/ZiProto/TypeTransformer/Extension.php',
                'ncc\\ncc\\ziproto\\typetransformer\\validator' => '/Extensions/ZiProto/TypeTransformer/Validator.php',
                'ncc\\objects\\clihelpsection' => '/Objects/CliHelpSection.php',
                'ncc\\objects\\nccupdateinformation' => '/Objects/NccUpdateInformation.php',
                'ncc\\objects\\nccversioninformation' => '/Objects/NccVersionInformation.php',
                'ncc\\objects\\projectconfiguration' => '/Objects/ProjectConfiguration.php',
                'ncc\\objects\\projectconfiguration\\assembly' => '/Objects/ProjectConfiguration/Assembly.php',
                'ncc\\objects\\projectconfiguration\\build' => '/Objects/ProjectConfiguration/Build.php',
                'ncc\\objects\\projectconfiguration\\buildconfiguration' => '/Objects/ProjectConfiguration/BuildConfiguration.php',
                'ncc\\objects\\projectconfiguration\\compiler' => '/Objects/ProjectConfiguration/Compiler.php',
                'ncc\\objects\\projectconfiguration\\dependency' => '/Objects/ProjectConfiguration/Dependency.php',
                'ncc\\objects\\projectconfiguration\\project' => '/Objects/ProjectConfiguration/Project.php',
                'ncc\\objects\\vault' => '/Objects/Vault.php',
                'ncc\\objects\\vault\\defaultentry' => '/Objects/Vault/DefaultEntry.php',
                'ncc\\objects\\vault\\entry' => '/Objects/Vault/Entry.php',
                'ncc\\symfony\\component\\process\\exception\\exceptioninterface' => '/ThirdParty/Symfony/Process/Exception/ExceptionInterface.php',
                'ncc\\symfony\\component\\process\\exception\\invalidargumentexception' => '/ThirdParty/Symfony/Process/Exception/InvalidArgumentException.php',
                'ncc\\symfony\\component\\process\\exception\\logicexception' => '/ThirdParty/Symfony/Process/Exception/LogicException.php',
                'ncc\\symfony\\component\\process\\exception\\processfailedexception' => '/ThirdParty/Symfony/Process/Exception/ProcessFailedException.php',
                'ncc\\symfony\\component\\process\\exception\\processsignaledexception' => '/ThirdParty/Symfony/Process/Exception/ProcessSignaledException.php',
                'ncc\\symfony\\component\\process\\exception\\processtimedoutexception' => '/ThirdParty/Symfony/Process/Exception/ProcessTimedOutException.php',
                'ncc\\symfony\\component\\process\\exception\\runtimeexception' => '/ThirdParty/Symfony/Process/Exception/RuntimeException.php',
                'ncc\\symfony\\component\\process\\executablefinder' => '/ThirdParty/Symfony/Process/ExecutableFinder.php',
                'ncc\\symfony\\component\\process\\inputstream' => '/ThirdParty/Symfony/Process/InputStream.php',
                'ncc\\symfony\\component\\process\\phpexecutablefinder' => '/ThirdParty/Symfony/Process/PhpExecutableFinder.php',
                'ncc\\symfony\\component\\process\\phpprocess' => '/ThirdParty/Symfony/Process/PhpProcess.php',
                'ncc\\symfony\\component\\process\\pipes\\abstractpipes' => '/ThirdParty/Symfony/Process/Pipes/AbstractPipes.php',
                'ncc\\symfony\\component\\process\\pipes\\pipesinterface' => '/ThirdParty/Symfony/Process/Pipes/PipesInterface.php',
                'ncc\\symfony\\component\\process\\pipes\\unixpipes' => '/ThirdParty/Symfony/Process/Pipes/UnixPipes.php',
                'ncc\\symfony\\component\\process\\pipes\\windowspipes' => '/ThirdParty/Symfony/Process/Pipes/WindowsPipes.php',
                'ncc\\symfony\\component\\process\\process' => '/ThirdParty/Symfony/Process/Process.php',
                'ncc\\symfony\\component\\process\\processutils' => '/ThirdParty/Symfony/Process/ProcessUtils.php',
                'ncc\\symfony\\component\\uid\\abstractuid' => '/ThirdParty/Symfony/uid/AbstractUid.php',
                'ncc\\symfony\\component\\uid\\binaryutil' => '/ThirdParty/Symfony/uid/BinaryUtil.php',
                'ncc\\symfony\\component\\uid\\factory\\namebaseduuidfactory' => '/ThirdParty/Symfony/uid/Factory/NameBasedUuidFactory.php',
                'ncc\\symfony\\component\\uid\\factory\\randombaseduuidfactory' => '/ThirdParty/Symfony/uid/Factory/RandomBasedUuidFactory.php',
                'ncc\\symfony\\component\\uid\\factory\\timebaseduuidfactory' => '/ThirdParty/Symfony/uid/Factory/TimeBasedUuidFactory.php',
                'ncc\\symfony\\component\\uid\\factory\\ulidfactory' => '/ThirdParty/Symfony/uid/Factory/UlidFactory.php',
                'ncc\\symfony\\component\\uid\\factory\\uuidfactory' => '/ThirdParty/Symfony/uid/Factory/UuidFactory.php',
                'ncc\\symfony\\component\\uid\\nilulid' => '/ThirdParty/Symfony/uid/NilUlid.php',
                'ncc\\symfony\\component\\uid\\niluuid' => '/ThirdParty/Symfony/uid/NilUuid.php',
                'ncc\\symfony\\component\\uid\\ulid' => '/ThirdParty/Symfony/uid/Ulid.php',
                'ncc\\symfony\\component\\uid\\uuid' => '/ThirdParty/Symfony/uid/Uuid.php',
                'ncc\\symfony\\component\\uid\\uuidv1' => '/ThirdParty/Symfony/uid/UuidV1.php',
                'ncc\\symfony\\component\\uid\\uuidv3' => '/ThirdParty/Symfony/uid/UuidV3.php',
                'ncc\\symfony\\component\\uid\\uuidv4' => '/ThirdParty/Symfony/uid/UuidV4.php',
                'ncc\\symfony\\component\\uid\\uuidv5' => '/ThirdParty/Symfony/uid/UuidV5.php',
                'ncc\\symfony\\component\\uid\\uuidv6' => '/ThirdParty/Symfony/uid/UuidV6.php',
                'ncc\\utilities\\functions' => '/Utilities/Functions.php',
                'ncc\\utilities\\pathfinder' => '/Utilities/PathFinder.php',
                'ncc\\utilities\\resolver' => '/Utilities/Resolver.php',
                'ncc\\utilities\\security' => '/Utilities/Security.php',
                'ncc\\utilities\\validate' => '/Utilities/Validate.php',
                'ncc\\ziproto\\abstracts\\options' => '/Extensions/ZiProto/Abstracts/Options.php',
                'ncc\\ziproto\\abstracts\\regex' => '/Extensions/ZiProto/Abstracts/Regex.php',
                'ncc\\ziproto\\bufferstream' => '/Extensions/ZiProto/BufferStream.php',
                'ncc\\ziproto\\decodingoptions' => '/Extensions/ZiProto/DecodingOptions.php',
                'ncc\\ziproto\\encodingoptions' => '/Extensions/ZiProto/EncodingOptions.php',
                'ncc\\ziproto\\exception\\decodingfailedexception' => '/Extensions/ZiProto/Exception/DecodingFailedException.php',
                'ncc\\ziproto\\exception\\encodingfailedexception' => '/Extensions/ZiProto/Exception/EncodingFailedException.php',
                'ncc\\ziproto\\exception\\insufficientdataexception' => '/Extensions/ZiProto/Exception/InsufficientDataException.php',
                'ncc\\ziproto\\exception\\integeroverflowexception' => '/Extensions/ZiProto/Exception/IntegerOverflowException.php',
                'ncc\\ziproto\\exception\\invalidoptionexception' => '/Extensions/ZiProto/Exception/InvalidOptionException.php',
                'ncc\\ziproto\\ext' => '/Extensions/ZiProto/Ext.php',
                'ncc\\ziproto\\packet' => '/Extensions/ZiProto/Packet.php',
                'ncc\\ziproto\\type\\binary' => '/Extensions/ZiProto/Type/Binary.php',
                'ncc\\ziproto\\type\\map' => '/Extensions/ZiProto/Type/Map.php',
                'ncc\\ziproto\\typetransformer\\maptransformer' => '/Extensions/ZiProto/TypeTransformer/MapTransformer.php',
                'ncc\\ziproto\\ziproto' => '/Extensions/ZiProto/ZiProto.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd
