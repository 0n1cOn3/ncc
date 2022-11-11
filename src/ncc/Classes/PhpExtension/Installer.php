<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace ncc\Classes\PhpExtension;

    use ArrayIterator;
    use Exception;
    use ncc\Abstracts\ComponentDataType;
    use ncc\Abstracts\ComponentFileExtensions;
    use ncc\Exceptions\AccessDeniedException;
    use ncc\Exceptions\ComponentChecksumException;
    use ncc\Exceptions\ComponentDecodeException;
    use ncc\Exceptions\FileNotFoundException;
    use ncc\Exceptions\IOException;
    use ncc\Exceptions\NoUnitsFoundException;
    use ncc\Exceptions\ResourceChecksumException;
    use ncc\Exceptions\UnsupportedComponentTypeException;
    use ncc\Interfaces\InstallerInterface;
    use ncc\Objects\InstallationPaths;
    use ncc\Objects\Package;
    use ncc\Objects\Package\Component;
    use ncc\ThirdParty\nikic\PhpParser\Comment;
    use ncc\ThirdParty\nikic\PhpParser\Node;
    use ncc\ThirdParty\nikic\PhpParser\NodeDumper;
    use ncc\ThirdParty\theseer\Autoload\CollectorException;
    use ncc\ThirdParty\theseer\Autoload\CollectorResult;
    use ncc\ThirdParty\theseer\Autoload\Config;
    use ncc\ThirdParty\theseer\Autoload\Factory;
    use ncc\Utilities\Base64;
    use ncc\Utilities\Console;
    use ncc\Utilities\IO;
    use ReflectionClass;
    use ReflectionException;
    use RuntimeException;
    use SplFileInfo;
    use function is_array;
    use function is_string;

    class Installer implements InstallerInterface
    {
        /**
         * @var ReflectionClass[] Node type to reflection class map
         */
        private $reflectionClassCache;

        /**
         * @var Package
         */
        private $package;

        /**
         * @inheritDoc
         */
        public function __construct(Package $package)
        {
            $this->package = $package;
        }

        /**
         * Processes the given component and returns the decoded component as a string representation
         * If the processed component does not result in a string representation, none will be returned.
         *
         * @param Component $component
         * @return string|null
         * @throws ComponentChecksumException
         * @throws ComponentDecodeException
         * @throws UnsupportedComponentTypeException
         */
        public function processComponent(Package\Component $component): ?string
        {
            if($component->Data == null)
                return null;

            if(!$component->validateChecksum())
                throw new ComponentChecksumException('Checksum validation failed for component ' . $component->Name . ', the package may be corrupted.');

            switch($component->DataType)
            {
                case ComponentDataType::AST:
                    try
                    {
                        $decoded_component = $this->decodeRecursive($component->Data);
                    }
                    catch (Exception $e)
                    {
                        throw new ComponentDecodeException('Cannot decode component: ' . $component->Name . ', ' . $e->getMessage(), $e);
                    }

                    $dumper = new NodeDumper();
                    return $dumper->dump($decoded_component);

                case ComponentDataType::b64encoded:
                   return Base64::decode($component->Data);

                case ComponentDataType::Plain:
                    return $component->Data;

                default:
                    throw new UnsupportedComponentTypeException('Unsupported component type \'' . $component->DataType . '\'');
            }
        }

        /**
         * @inheritDoc
         */
        public function preInstall(InstallationPaths $installationPaths): void
        {
        }

        /**
         * @inheritDoc
         */
        public function postInstall(InstallationPaths $installationPaths): void
        {
            Console::out('Generating autoloader');
            Console::out('theseer\autoload - Copyright (c) 2010-2016 Arne Blankerts <arne@blankerts.de> and Contributors');
            Console::out('theseer\DirectoryScanner - Copyright (c) 2009-2014 Arne Blankerts <arne@blankerts.de> All rights reserved.');
            $autoload_path = $installationPaths->getBinPath() . DIRECTORY_SEPARATOR . 'autoload.php';
            $autoload_src = $this->generateAutoload($installationPaths->getSourcePath(), $autoload_path);
            IO::fwrite($autoload_path, $autoload_src);
        }

        /**
         * Processes the given resource and returns the string representation of the resource
         *
         * @param Package\Resource $resource
         * @return string|null
         * @throws ResourceChecksumException
         */
        public function processResource(Package\Resource $resource): ?string
        {
            if(!$resource->validateChecksum())
                throw new ResourceChecksumException('Checksum validation failed for resource ' . $resource->Name . ', the package may be corrupted.');
            return $resource->Data;
        }

        /**
         * @param $value
         * @return array|Comment|Node
         * @throws ReflectionException
         * @noinspection PhpMissingReturnTypeInspection
         */
        private function decodeRecursive($value)
        {
            if (is_array($value))
            {
                if (isset($value['nodeType']))
                {
                    if ($value['nodeType'] === 'Comment' || $value['nodeType'] === 'Comment_Doc')
                    {
                        return $this->decodeComment($value);
                    }

                    return $this->decodeNode($value);
                }

                return $this->decodeArray($value);
            }

            return $value;
        }

        /**
         * @param array $array
         * @return array
         * @throws ReflectionException
         */
        private function decodeArray(array $array) : array
        {
            $decodedArray = [];
            foreach ($array as $key => $value)
            {
                $decodedArray[$key] = $this->decodeRecursive($value);
            }
            return $decodedArray;
        }

        /**
         * @param array $value
         * @return Node
         * @throws ReflectionException
         */
        private function decodeNode(array $value): Node
        {
            $nodeType = $value['nodeType'];

            if(!is_string($nodeType))
            {
                throw new RuntimeException('Node type must be a string');
            }

            $reflectionClass = $this->reflectionClassFromNodeType($nodeType);
            /** @var Node $node */
            $node = $reflectionClass->newInstanceWithoutConstructor();

            if (isset($value['attributes']))
            {
                if (!is_array($value['attributes']))
                {
                    throw new RuntimeException('Attributes must be an array');
                }

                $node->setAttributes($this->decodeArray($value['attributes']));
            }

            foreach ($value as $name => $subNode)
            {
                if ($name === 'nodeType' || $name === 'attributes')
                {
                    continue;
                }

                $node->$name = $this->decodeRecursive($subNode);
            }

            return $node;
        }

        /**
         * @param array $value
         * @return Comment
         */
        private function decodeComment(array $value): Comment
        {
            $className = $value['nodeType'] === 'Comment' ? Comment::class : Comment\Doc::class;

            if (!isset($value['text']))
            {
                throw new RuntimeException('Comment must have text');
            }

            return new $className(
                $value['text'],
                $value['line'] ?? -1, $value['filePos'] ?? -1, $value['tokenPos'] ?? -1,
                $value['endLine'] ?? -1, $value['endFilePos'] ?? -1, $value['endTokenPos'] ?? -1
            );
        }

        /**
         * @param string $nodeType
         * @return ReflectionClass
         * @throws ReflectionException
         */
        private function reflectionClassFromNodeType(string $nodeType): ReflectionClass
        {
            if (!isset($this->reflectionClassCache[$nodeType]))
            {
                $className = $this->classNameFromNodeType($nodeType);
                $this->reflectionClassCache[$nodeType] = new ReflectionClass($className);
            }

            return $this->reflectionClassCache[$nodeType];
        }

        /**
         * @param string $nodeType
         * @return string
         */
        private function classNameFromNodeType(string $nodeType): string
        {
            $className = 'PhpParser\\Node\\' . strtr($nodeType, '_', '\\');
            if (class_exists($className))
            {
                return $className;
            }

            $className .= '_';
            if (class_exists($className))
            {
                return $className;
            }

            throw new RuntimeException("Unknown node type \"$nodeType\"");
        }


        /**
         * Processes the project and generates the autoloader source code.
         *
         * @param string $src
         * @param string $output
         * @return string
         * @throws AccessDeniedException
         * @throws CollectorException
         * @throws FileNotFoundException
         * @throws IOException
         * @throws NoUnitsFoundException
         */
        private function generateAutoload(string $src, string $output): string
        {
            // Construct configuration
            $configuration = new Config([$src]);
            $configuration->setFollowSymlinks(false); // Don't follow symlinks, it won't work on some systems.
            $configuration->setTrusting(false); // Paranoid
            $configuration->setOutputFile($output);
            $configuration->setStaticMode(false);
            // Official PHP file extensions that are missing from the default configuration (whatever)
            $configuration->setInclude(ComponentFileExtensions::Php);

            // Construct factory
            $factory = new Factory();
            $factory->setConfig($configuration);

            // Create Collector
            $result = self::runCollector($factory, $configuration);

            // Exception raises when there are no files in the project that can be processed by the autoloader
            if(!$result->hasUnits())
            {
                throw new NoUnitsFoundException('No units were found in the project');
            }

            if(!$result->hasDuplicates())
            {
                foreach($result->getDuplicates() as $unit => $files)
                {
                    Console::outWarning((count($files) -1). ' duplicate unit(s) detected in the project: ' . $unit);
                }
            }

            $template = IO::fread($configuration->getTemplate());

            $builder = $factory->getRenderer($result);
            return $builder->render($template);
        }

        /**
         * Iterates through the target directories through the collector and returns the collector results.
         *
         * @param Factory $factory
         * @param Config $config
         * @return CollectorResult
         * @throws CollectorException
         * @throws Exception
         */
        private static function runCollector(Factory $factory, Config $config): CollectorResult
        {
            $collector = $factory->getCollector();
            foreach($config->getDirectories() as $directory)
            {
                if(is_dir($directory))
                {
                    $scanner = $factory->getScanner()->getIterator($directory);
                    $collector->processDirectory($scanner);
                    unset($scanner);
                }
                else
                {
                    $file = new SplFileInfo($directory);
                    $filter = $factory->getFilter(new ArrayIterator(array($file)));
                    foreach($filter as $file)
                    {
                        $collector->processFile($file);
                    }
                }
            }

            return $collector->getResult();
        }
    }