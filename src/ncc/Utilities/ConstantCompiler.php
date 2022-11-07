<?php

    namespace ncc\Utilities;

    use ncc\Abstracts\SpecialConstants\BuildConstants;
    use ncc\Abstracts\SpecialConstants\InstallConstants;
    use ncc\Abstracts\SpecialConstants\ProjectConstants;
    use ncc\Objects\InstallationPaths;
    use ncc\Objects\Package;
    use ncc\Objects\ProjectConfiguration;

    class ConstantCompiler
    {
        /**
         * Compiles assembly constants about the project (Usually used during compiling time)
         *
         * @param string|null $input
         * @param ProjectConfiguration $projectConfiguration
         * @return string|null
         * @noinspection PhpUnnecessaryLocalVariableInspection
         */
        public static function compileProjectConstants(?string $input, ProjectConfiguration $projectConfiguration): ?string
        {
            if($input == null)
                return null;

            $input = str_replace(ProjectConstants::AssemblyName, $projectConfiguration->Assembly->Name, $input);
            $input = str_replace(ProjectConstants::AssemblyPackage, $projectConfiguration->Assembly->Package, $input);
            $input = str_replace(ProjectConstants::AssemblyDescription, $projectConfiguration->Assembly->Description, $input);
            $input = str_replace(ProjectConstants::AssemblyCompany, $projectConfiguration->Assembly->Company, $input);
            $input = str_replace(ProjectConstants::AssemblyProduct, $projectConfiguration->Assembly->Product, $input);
            $input = str_replace(ProjectConstants::AssemblyCopyright, $projectConfiguration->Assembly->Copyright, $input);
            $input = str_replace(ProjectConstants::AssemblyTrademark, $projectConfiguration->Assembly->Trademark, $input);
            $input = str_replace(ProjectConstants::AssemblyVersion, $projectConfiguration->Assembly->Version, $input);
            $input = str_replace(ProjectConstants::AssemblyUid, $projectConfiguration->Assembly->UUID, $input);

            return $input;
        }

        /**
         * Compiles build constants about the NCC build (Usually used during compiling time)
         *
         * @param string|null $input
         * @return string|null
         * @noinspection PhpUnnecessaryLocalVariableInspection
         */
        public static function compileBuildConstants(?string $input): ?string
        {
            if($input == null)
                return null;

            $input = str_replace(BuildConstants::CompileTimestamp, time(), $input);
            $input = str_replace(BuildConstants::NccBuildVersion, NCC_VERSION_NUMBER, $input);
            $input = str_replace(BuildConstants::NccBuildFlags, explode(' ', NCC_VERSION_FLAGS), $input);
            $input = str_replace(BuildConstants::NccBuildBranch, NCC_VERSION_BRANCH, $input);

            return $input;
        }

        /**
         * Compiles installation constants (Usually used during compiling time)
         *
         * @param string|null $input
         * @param InstallationPaths $installationPaths
         * @return string|null
         * @noinspection PhpUnnecessaryLocalVariableInspection
         */
        public static function compileInstallConstants(?string $input, InstallationPaths $installationPaths): ?string
        {
            if($input == null)
                return null;

            $input = str_replace($installationPaths->getInstallationPath(), InstallConstants::InstallationPath, $input);
            $input = str_replace($installationPaths->getBinPath(), InstallConstants::BinPath, $input);
            $input = str_replace($installationPaths->getSourcePath(), InstallConstants::SourcePath, $input);
            $input = str_replace($installationPaths->getDataPath(), InstallConstants::DataPath, $input);

            return $input;
        }
    }