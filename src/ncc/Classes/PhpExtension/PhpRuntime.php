<?php

    namespace ncc\Classes\PhpExtension;

    use Exception;
    use ncc\Abstracts\Options\RuntimeImportOptions;
    use ncc\Classes\NccExtension\ConstantCompiler;
    use ncc\Exceptions\ConstantReadonlyException;
    use ncc\Exceptions\ImportException;
    use ncc\Exceptions\InvalidConstantNameException;
    use ncc\Interfaces\RuntimeInterface;
    use ncc\Objects\PackageLock\VersionEntry;
    use ncc\Objects\ProjectConfiguration\Assembly;
    use ncc\Runtime\Constants;
    use ncc\Utilities\IO;
    use ncc\ZiProto\ZiProto;

    class PhpRuntime implements RuntimeInterface
    {

        /**
         * Attempts to import a PHP package
         *
         * @param VersionEntry $versionEntry
         * @param array $options
         * @return bool
         * @throws ImportException
         */
        public static function import(VersionEntry $versionEntry, array $options=[]): bool
        {
            $autoload_path = $versionEntry->getInstallPaths()->getBinPath() . DIRECTORY_SEPARATOR . 'autoload.php';
            $static_files = $versionEntry->getInstallPaths()->getBinPath() . DIRECTORY_SEPARATOR . 'static_autoload.bin';
            $constants_path = $versionEntry->getInstallPaths()->getDataPath() . DIRECTORY_SEPARATOR . 'const';
            $assembly_path = $versionEntry->getInstallPaths()->getDataPath() . DIRECTORY_SEPARATOR . 'assembly';

            if(!file_exists($assembly_path))
                throw new ImportException('Cannot locate assembly file \'' . $assembly_path . '\'');

            try
            {
                $assembly_content = ZiProto::decode(IO::fread($assembly_path));
                $assembly = Assembly::fromArray($assembly_content);
            }
            catch(Exception $e)
            {
                throw new ImportException('Failed to load assembly file \'' . $assembly_path . '\': ' . $e->getMessage());
            }

            if(file_exists($constants_path))
            {
                try
                {
                    $constants = ZiProto::decode(IO::fread($constants_path));
                }
                catch(Exception $e)
                {
                    throw new ImportException('Failed to load constants file \'' . $constants_path . '\': ' . $e->getMessage());
                }

                foreach($constants as $name => $value)
                {
                    $value = ConstantCompiler::compileRuntimeConstants($value);

                    try
                    {
                        Constants::register($assembly->Package, $name, $value, true);
                    }
                    catch (ConstantReadonlyException $e)
                    {
                        trigger_error('Constant \'' . $name . '\' is readonly (' . $assembly->Package . ')', E_USER_WARNING);
                    }
                    catch (InvalidConstantNameException $e)
                    {
                        throw new ImportException('Invalid constant name \'' . $name . '\' (' . $assembly->Package . ')', $e);
                    }
                }
            }

            if(file_exists($autoload_path) && !in_array(RuntimeImportOptions::ImportAutoloader, $options))
            {
                require_once($autoload_path);
            }

            if(file_exists($static_files) && !in_array(RuntimeImportOptions::ImportStaticFiles, $options))
            {
                try
                {
                    $static_files = ZiProto::decode(IO::fread($static_files));
                    foreach($static_files as $file)
                        require_once($file);
                }
                catch(Exception $e)
                {
                    throw new ImportException('Failed to load static files: ' . $e->getMessage(), $e);
                }

            }

            if(!file_exists($autoload_path) && !file_exists($static_files))
                return false;

            return true;
        }
    }