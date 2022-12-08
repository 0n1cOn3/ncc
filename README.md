# ![NCC](assets/icon/ncc_32px.png "NCC")   NCC

Nosial Code Compiler is a program written in PHP designed to be a multi-purpose compiler, package manager and toolkit.
This program is a complete re-write of the now defunct [PHP Package Manager (PPM)](https://git.n64.cc/intellivoid/ppm)
toolkit offering more features, security and proper code licensing and copyrighting for the components used for the project.

### Alpha Stage

NCC is currently in alpha stage, meaning that it's not fully functional and may not work on your system. If you find any bugs
or issues please report them to the [GitHub Issue Tracker](https://git.n64.cc/intellivoid/ncc/issues).

At the moment NCC is currently being used while developing other software, this serves as a test run to
improve on changes for the next version.

### Notes

 > Compiler extensions requires their own set of dependencies to be met, for example Java compilers will require JDK

 > NCC is designed to run only on a PHP 8.0+ environment, compiler extensions can have support for different PHP versions.

 > Third-party dependencies and included libraries has a dedicated namespace for `ncc` to avoid user land conflicts if
 > the user wishes to install and use one of the same dependencies that NCC uses.

## Authors
 - Zi Xing Narrakas (netkas) <[netkas@n64.cc](mailto:netkas@64.cc)>

## Special Thanks
 - Marc Gutt (mgutt) <[marc@gutt.it](mailto:marc@gutt.it)>
 - Debusschère Alexandre ([debuss](https://github.com/debuss)) 

## Copyright
- Copyright (c) 2022-2022, Nosial - All Rights Reserved

# Licenses

NCC is licensed under the MIT License, see [LICENSE](LICENSE) for more information.

Multiple licenses for the open source components used in this
project can be found at [LICENSE](LICENSES)