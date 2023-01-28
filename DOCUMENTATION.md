# NCC Documentation

This document serves the purpose of presenting the documentation for using/developing
NCC, from basic installation, basic usage, standards and much more.

## Table of contents

<!-- TOC -->
* [NCC Documentation](#ncc-documentation)
  * [Table of contents](#table-of-contents)
  * [Introduction](#introduction)
  * [What is NCC?](#what-is-ncc)
* [Building NCC from source](#building-ncc-from-source)
  * [Requirements to build](#requirements-to-build)
  * [Installing phpab](#installing-phpab)
  * [Building NCC](#building-ncc)
    * [Redist](#redist)
    * [Tar](#tar)
* [Installing NCC](#installing-ncc)
  * [Command line arguments](#command-line-arguments)
* [Uninstalling NCC](#uninstalling-ncc)
* [Projects](#projects)
  * [Creating a project](#creating-a-project)
  * [project.json structure](#projectjson-structure)
    * [project](#project)
    * [project.compiler](#projectcompiler)
    * [project.update_source](#projectupdatesource)
* [Remote Sources](#remote-sources)
  * [Supported sources](#supported-sources)
  * [Default sources](#default-sources)
  * [Managing sources](#managing-sources)
    * [Adding a source](#adding-a-source)
    * [Removing a source](#removing-a-source)
    * [Listing sources](#listing-sources)
  * [Credential Management](#credential-management)
    * [Adding credentials](#adding-credentials)
    * [Removing credentials](#removing-credentials)
    * [Listing credentials](#listing-credentials)
* [Naming a package](#naming-a-package)
  * [Naming conventions](#naming-conventions)
  * [References](#references)
<!-- TOC -->

## Introduction

This section serves the basic introduction of NCC, what it's used for and how you can use it in your own projects or use 
it to run and build other projects that are designed to be used with NCC. 

## What is NCC?

NCC (*Acronym for **N**osial **C**ode **C**ompiler*) is a multi-purpose compiler, package manager and toolkit. Allowing 
projects to be managed and built more easily without having to mess with all the traditional tools that comes with your 
language of choice. Right now NCC only supports PHP as it's written in PHP but extensions for other languages/frameworks
can be built into the software in the future when the need comes for it.

NCC can make the process of building your code into a redistributable package much more efficient by treating each 
building block of your project as a component that is interconnected in your environment instead of the more popular 
route taken by package/dependency managers such as [composer](https://getcomposer.org/),[npm](https://www.npmjs.com/) or 
[pypi (or pip)](https://pypi.org/).


------------------------------------------------------------------------------------

# Building NCC from source

Building NCC from source is easy with very few requirements to start building. At the moment ncc can only be debugged or
tested  by building a redistributable source and installing it.

## Requirements to build

- php8.0+
- php-mbstring
- php-ctype
- php-common (covers tokenizer & posix among others)
- make
- phpab
- tar *(optional)*

## Installing phpab

phpab is also known as [PHP Autoload Builder](https://github.com/theseer/Autoload), phpab is an open source tool used 
for creating autoload files, ncc needs this tool in order to generate it's autoload files whenever there's any changes
to its source code.

This tool is only required for building and or creating a redistributable package of ncc. This component is not
required to be installed to use ncc.

for some components that require static loading, ncc will automatically load it using its own 
[autoloader](src/autoload/autoload.php)

The recommended way to install phpab is by using [phive](https://phar.io/), if you don't have phive installed you can 
install it by running these commands in your terminal (from the official documentation)

```shell
wget -O phive.phar https://phar.io/releases/phive.phar
wget -O phive.phar.asc https://phar.io/releases/phive.phar.asc
gpg --keyserver hkps://keys.openpgp.org --recv-keys 0x9D8A98B29B2D5D79
gpg --verify phive.phar.asc phive.phar
chmod +x phive.phar
sudo mv phive.phar /usr/local/bin/phive
```

Once phive is installed, you can run the final command to install phpab

```shell
sudo phive install phpab --global
```

or you can run this command to install it locally

```shell
phive install phpab
```

**Note:** Optionally, you may want to have `phab` available in your `$PATH`, this can be done with this command. 
*(Replace `x.xx.x` with your version number)* this is if you installed it locally

```shell
ln -s /home/user/.phive/phars/phpab-x.xx.x.phar /usr/local/bin/phpab
```

## Building NCC

First, navigate to the main directory of NCC's source code where the [Makefile](Makefile) is present. If you
already attempted to or had built ncc before, it's  recommended to use `make clean` before building.

### Redist

Running `redist` from the Makefile will generate all the required autoloader for ncc and move all the required files 
into one redistributable source folder under a directory called `build/src`

```shell
make redist
```


### Tar

Running `tar` will run redist before packaging the redistributable source into a tar.gz file that can be distributed to 
other machines, this process is not a requirement.

```shell
make tar
```

Once you have a populated `build/src` folder, you can simply run execute the `installer` file to install your build of 
ncc onto the running machine.

------------------------------------------------------------------------------------

# Installing NCC

Installing NCC is easy, you can either download the redistributable source from the [releases](https://git.n64.cc/nosial/ncc/-/releases)
page or you can build it from source using the instructions above.

Once you have the redistributable source, you can simply run execute the `INSTALL` file to install ncc onto the running 
machine.

## Command line arguments

The installer accepts a few command line arguments that can be used to customize the installation process.

`--help` Displays the help message

`--auto` Automatically installs ncc without asking for user input.

**Note:** To install composer along with ncc, you must also provide the `--install-composer` argument.

`--install-composer` Installs composer along with ncc. By default, ncc will not install composer and during the
installation process  you will be asked if you want to install composer along-side ncc, this will not conflict
with any existing composer installation.

`--install-dir` Specifies the directory where ncc will be installed to.  By default, ncc will be installed to `/etc/ncc`

`--bypass-cli-check` Bypasses the check in the installer that checks if the installer is being run from the command
line, this is useful if you want to install ncc from a script.

`--bypass-checksum` Bypasses the checksum check in the installer, this is useful if you made modifications to the 
installation files and want to install a modified version of ncc.

But this isn't recommended and the proper way to do this is to modify the source code and build ncc from source,
the Makefile task will automatically rebuild the checksum file for you.


------------------------------------------------------------------------------------

# Uninstalling NCC

Uninstalling NCC is easy, simply delete the directory where ncc was installed to, by default this is `/etc/ncc`.

It's recommended to run `ncc package --uninstall-all` before uninstalling ncc, this will uninstall all the packages
that were installed using ncc and remove any artifacts that were created by these packages.

**Note:**
 - To delete all the data that ncc has created, you can also delete the `/var/ncc` directory.
 - Finally, remove the symlink that was created in `/usr/local/bin`to the `ncc` entry point file.

------------------------------------------------------------------------------------

# Projects

A project is a directory that contains all the source files to your program, it's similar to a workspace in other IDEs.
Usually contains a `project.json` file which contains all the information about the project that ncc needs to know.

This can include the name of the program, the version of the program, the author of the program, the dependencies of the
program, build configurations, and more.

This section will cover the basics of creating a project and managing it and the technical details of the `project.json` 
file.


## Creating a project

This is the first step in using ncc, you must create a project before you can do anything else (*not really because you
can install packages without needing to create a project and run them directly, but you get the point*)

The NCC command-line tool provides a management command called `project` that can be used to create a new project
or to manage an existing project.

```shell
ncc project create --package "com.example.program" --name "Example Program"
```

This command will create a new project in the current directory, the `--package` argument specifies the package name of 
the project, this is used to identify the project and to avoid conflicts with other projects that may have the same name.

The `--name` argument specifies the name of the project, this is used to display the name of the project in the project
manager and in the project settings. This doesn't have to be the same as the package name or unique.

**Note:** If the options are not provided, the command will prompt you for the package name and the project name.

For more information about the project command, you can run `ncc project --help` to display the help message.

## project.json structure

The `project.json` file is a JSON file that contains all the information about the project.

When a project is created, the `project.json` file is automatically created and populated with the default values, you 
can modify this file to change the default values or to add more information about the project.

This section will go over the structure of the `project.json` file and what each field does.

### project

The `project` field contains information about the project, such as what compiler extension to use, options to pass on
to the compiler, and more.

| Name          | Type                                 | Required | Description                                                                                        |
|---------------|--------------------------------------|----------|----------------------------------------------------------------------------------------------------|
| compiler      | [project.compiler](#projectcompiler) | Yes      | The compiler extension that the project uses to compile the program                                |
| options       | `array`                              | No       | An array of options to pass on to the compiler, the options vary depending on the compiler and NCC |
| update_source | `project.update_source`              | No       | The source for where the program can fetch updates from                                            |

### project.compiler

The `project.compiler` field contains information about the compiler extension that the project uses to compile
the program.

| Name            | Type     | Required | Description                                                                                    |
|-----------------|----------|----------|------------------------------------------------------------------------------------------------|
| extension       | `string` | Yes      | The name of the compiler extension that the project uses to compile the program                |
| minimum_version | `string` | No       | The minimum version of the compiler extension that the project requires to compile the program |
| maximum_version | `string` | No       | The maximum version of the compiler extension that the project requires to compile the program |

### project.update_source

The `project.update_source` field contains information about the source where the program can fetch updates from.

| Name | Type     | Required | Description                                               |
|------|----------|----------|-----------------------------------------------------------|
|source| `string` | Yes      | The source where the program can fetch updates from, see  |

------------------------------------------------------------------------------------

# Remote Sources

Remote Sources are the locations where packages can be downloaded from, they are similar to repositories in other package
managers. They follow a simple syntax that allows you to specify the type of source, the location of the source, and more.

Examples of sources are:

- `symfony/process=latest@composer` - This is a package from the `symfony/process` package from the `composer` source
- `nosial/libs.config=latest@n64` - This is a package from the `nosial/libs.config` package from the `git.n64.cc` source

A full example syntax may look like this:

```
<vendor>/<package>:<branch>=<version>@<source name>
```

This syntax is used to specify a package from a source, the syntax is split into 4 parts:

- The vendor of the package
- The name of the package
- The branch of the package (optional)
- The version of the package (optional)
- The name of the source (needs to be configured in ncc)

## Supported sources

NCC supports the following sources:

- `github` - This source uses the GitHub API to fetch packages from GitHub (Included in the default sources)
- `gitlab` - This source uses the GitLab API to fetch packages from GitLab (Can be used with self-hosted GitLab instances)

Additional support for other sources will be added in the future.

## Default sources

NCC comes with a few default sources that are configured by default, these sources are:

- packagist.org (`composer`) **Note:** This is an internal source that uses `composer` to fetch packages from packagist.org.
  this is not configurable by the user.
- api.github.com (`github`)
- gitlab.com (`gitlab`)
- git.n64.cc (`n64`)
- gitgud.io (`gitgud`)

Additional sources can be added by the user. See [Adding a source](#adding-a-source) for more information.

## Managing sources

You can manage sources using the `source` command in the ncc command-line tool. This command can be used to add, remove,
and list sources. For more information about the `source` command, you can run `ncc source --help` to display the help
message.

### Adding a source

To add a source, you can use the `add` command in the ncc `source` command-line tool.

```shell
ncc source add --name "github" --type "github" --host "github.com" --ssl
```

This command will add a new source called `github` with the type `github` and the host `github.com`, the `--ssl` option
will tell ncc to use HTTPS instead of HTTP when fetching packages from this source.

The reason to specify the type of source is to tell ncc what API to use when fetching packages from this source, for
example if you specify the type as `github` then ncc will use the GitHub API to fetch packages from this source so it's
important to specify the correct type when adding a source.

> **Note:** You need root permissions to add a source


### Removing a source

To remove a source, you can use the `remove` command in the ncc `source` command-line tool.

```shell
ncc source remove --name "github"
```

> **Note:** You need root permissions to remove a source

> **Note:** Removing a source also removes the ability for some packages to be fetched or updated from this source


### Listing sources

To list all the sources, you can use the `list` command in the ncc `source` command-line tool.

```shell
ncc source list
```

## Credential Management

Some sources require credentials to be able to fetch packages from them, for example the `gitlab` source requires
credentials to be able to fetch packages from a self-hosted GitLab instance. NCC supports storing credentials for
sources in a secure way using the `cred` command in the ncc command-line tool.

### Adding credentials

To add credentials for a source, you can use the `add` command in the ncc `cred` command-line tool.

```shell
ncc cred add --alias "My Alias" --auth-type login --username "myusername" --password "mypassword"
```

To add a private access token as a credential, you can specify the `--auth-type` as `pat` and specify the token as
`--token` instead of providing `--username` and `--password`.

```shell
ncc cred add --alias "My Alias" --auth-type pat --token="mytoken"
```

By default, ncc will encrypt the entry except for the alias using the password/token that you provide.

However, because it's encrypted you will need to provide the password/token when using the credential since ncc will
not be able to decrypt the entry without a password. To avoid being asked for the password/token every time you use the
credential, you can pass on the `--no-encryption` option to the `cred` command-line tool.

```shell
ncc cred add --alias "My Alias" --auth-type login --username "myusername" --password "mypassword" --no-encryption
```

Encryption is applied individually to each credential, so you can have some credentials encrypted and some not encrypted.

> **Note:** You need root permissions to add credentials


### Removing credentials

To remove credentials, you can use the `remove` command in the ncc `cred` command-line tool.

```shell
ncc cred remove --alias "My Alias"
```

> **Note:** You need root permissions to remove credentials


### Listing credentials

To list all the credentials, you can use the `list` command in the ncc `cred` command-line tool. this will return
a list of all the credentials that are stored in the credential store with additional information about each entry.

```shell
ncc cred list
```

------------------------------------------------------------------------------------

# Naming a package

NCC Follows the same naming convention as Java's naming convention. The purpose of naming a package this way is
to easily create a "Name" of the package, this string of information contains

- The developer/organization behind the package
- The package name itself


## Naming conventions

Package names are written in all lower-case due to the fact that some operating systems treats file names
differently, for example on Linux `Aa.txt` and `aa.txt`are two entirely different file names because of the
capitalization and on Windows it's treated as the same file name.

Organizations or small developers use their domain name in reverse to begin their package names, for example
`net.nosial.example` is a package named `example` created by a programmer at `nosial.net`

Just like the Java naming convention, to avoid conflicts of the same package name developers can use something
different, for example as pointed out in Java's package naming convention developers can instead use something 
like a region to name packages, for example `net.nosial.region.example`


## References

For Java's package naming conventions see [Naming a Package](https://docs.oracle.com/javase/tutorial/java/package/namingpkgs.html)
from the Oracle's Java documentation resource, as the same rules apply to NCC except for *some* illegal naming
conventions such as packages not being able to begin with `int` or numbers