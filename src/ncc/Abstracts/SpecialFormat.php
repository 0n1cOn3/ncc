<?php

    namespace ncc\Abstracts;

    abstract class SpecialFormat
    {
        /**
         * Assembly's Name Property
         */
        const AssemblyName = '%ASSEMBLY.NAME%';

        /**
         * Assembly's Package Property
         */
        const AssemblyPackage = '%ASSEMBLY.PACKAGE%';

        /**
         * Assembly's Description Property
         */
        const AssemblyDescription = '%ASSEMBLY.DESCRIPTION%';

        /**
         * Assembly's Company Property
         */
        const AssemblyCompany = '%ASSEMBLY.COMPANY%';

        /**
         * Assembly's Product Property
         */
        const AssemblyProduct = '%ASSEMBLY.PRODUCT%';

        /**
         * Assembly's Copyright Property
         */
        const AssemblyCopyright = '%ASSEMBLY.COPYRIGHT%';

        /**
         * Assembly's Trademark Property
         */
        const AssemblyTrademark = '%ASSEMBLY.TRADEMARK%';

        /**
         * Assembly's Version Property
         */
        const AssemblyVersion = '%ASSEMBLY.VERSION%';

        /**
         * Assembly's UUID property
         */
        const AssemblyUid = '%ASSEMBLY.UID%';

        /**
         * The Unix Timestamp for when the package was compield
         */
        const CompileTimestamp = '%COMPILE_TIMESTAMP%';

        /**
         * The version of NCC that was used to compile the package
         */
        const NccBuildVersion = '%NCC_BUILD_VERSION%';

        /**
         * NCC Build Flags exploded into spaces
         */
        const NccBuildFlags = '%NCC_BUILD_FLAGS%';

        /**
         * NCC Build Branch
         */
        const NccBuildBranch = '%NCC_BUILD_BRANCH%';
    }