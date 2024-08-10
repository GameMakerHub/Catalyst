
# Catalyst
> A Game Maker Studio 2 dependency manager.

> [!WARNING]  
> Please note that this tool does not support GMS2.3 and the newer versions. 2.3 development is currently in progress!

> [!CAUTION]  
> This tool is still in development. Make sure you have backups and / or version control.

[![Build Status](https://travis-ci.org/GameMakerHub/Catalyst.svg?branch=master)](https://travis-ci.org/GameMakerHub/Catalyst)
[![Coverage](https://codecov.io/gh/GameMakerHub/Catalyst/branch/master/graph/badge.svg)](https://codecov.io/gh/GameMakerHub/Catalyst)

# Table of contents:
 - [About](#about)
   - [Sharing](#sharing)
 - [Setup](#setup)
   - [Windows](#windows)
   - [Linux and OSX](#linux-and-osx)
 - [Usage](#usage)
   - [Example](#example)
   - [Arguments](#arguments)

## About

Catalyst is a tool to manage dependencies within GameMakerStudio2 projects, and speed up certain processes.
If you need a library inside of your project you can use this tool to declare the dependency and install all dependencies.
The dependencies will be recursively solved, so multiple libraries can require multiple dependencies, and they can be shared.

Installing dependencies will exclude them (to the best extent possible) from version control, keeping your source code clean.
Unfortunately, due to the format of Game Maker Studio 2 project, there will be some changes in the main project file
as well as some view files.

Inside the GameMaker project, all external files (vendored files) will reside in the `vendor` folders.

[Read a simple "Get Started" guide here](https://gamemakerhub.net/catalyst)

### Sharing
When other people check out your source, and it requires dependencies, it will not run. 
They will first have to run `catalyst install` to download and install the dependencies to the project.

This way we can make sure that vendored code is not included in version control.
Stating the obvious: files inside of the `vendor` folders should never be edited, as your changes are not included in VCS.
If you want to make changes to a vendored file, you will have to make a merge request for that package, and use that. This way, everybody can profit from the upgrades!

## Setup

### Windows
You can download a ready-to-run installer from [the releases page](https://github.com/GameMakerHub/Catalyst/releases). 
This installer will install the Catalyst source, along with its dependencies, so you can simply run it. 
It will also add the executable to your path, so you can run the `catalyst` program from anywhere.

#### Windows (from source)
You want to install PHP7.0+ and you might want to install a tool like Git Bash (that also has MINGW). 
This way you have a nice Linux-style CLI which you can work with.

If the command "php -v" doesn't work (can't find PHP) open up Git Bash as an admininistrator, and create the file "/usr/bin/php" with following contents:
```sh
#!/bin/bash
/c/php7.3/php.exe ${@:1:99}
```

where `/c/php7.3/php.exe` is your PHP's location ofcourse.

You also need to make sure you have added the following lines to the php.ini if they don't yet exist:
```ini
extension=php_curl.dll
extension=php_mbstring.dll
extension=php_openssl.dll
extension=php_sockets.dll
```

You also need to install composer (or download the .phar file into this directory), and then run `composer install` (or 
`php ./composer.phar install` if you dont have it installed)

### Linux and OSX

Make sure you have PHP7.3 and Composer installed. Clone this repository, run a `composer install` in this directory, 
and then you can use it.

## Usage

The windows installer adds `catalyst` to the path by default. You can run `catalyst` by typing it anywhere in the command line.

If you haven't got catalyst in your path (linux or OSX), you can execute the `index.php` file manually. 

### Install requirements
First initialize your project with Catalyst by writing a `catalyst.json` file, or using `catalyst init`.

Write requirements into the file, or use `catalyst require <pacakgename>`.

Run `catalyst install` to install all dependencies.

### Local directories as packages
If you don't want to share packages, you can add a directory repository in your `catalyst.json` file. 
Make sure that the project folders inside of the given folder contain a `catalyst.json` file!
Since there is no version information available through this setup, the system assumes "1.0.0". So if you want to 
require the package `../private-projects/My Tools/My Tools.yyp` you'll have create a `catalyst.json` with 
`"name": "private/my-tools"` and then run `catalyst require private/my-tools` in your main project, using a constraint that 
satisfies `1.0.0`; for example `*` or `>=1.0`. You can mix multiple repositories.

We now only support 1 extra type of repository, which is `directory`. So the format here is;
```json
"repositories": {
    "<relative file path>": "directory"
},
```

Example `catalyst.json` file:
```json
{
    "name": "dukesoft/other-project",
    "description": "Other project",
    "license": "MIT",
    "homepage": "https://github.com/robquistnl/other",
    "yyp": "OtherProject.yyp",
    "repositories": {
      "../private-projects": "directory"
    },
    "require": {
        "private/my-tools": "*",
        "dukesoft/dscpu-mercy": ">=1.2"
    }
}
``` 

The `private/my-tools` is just an arbitrary name for your private package. It can be anything you want, but must
conform the Catalyst package name format. `floop/doople` would work too - as long as it matches the `catalyst.json` 
file in your private project.

### Ignore files to be installed
Sometimes you can have a package that contains some non-related files for distribution. Testcases, test sprites, 
example rooms and whatnot can be resources that the users of a package do not need.

There are 3 types of "ignorable" resources. `resource`, `group` and `all`. 

You can use the `shell wildcard pattern` to mark resources from being installed into projects;

| wildcard | Explaination | Example | Matches |
|----------|--------------|---------|---------|
| `*` | Matches any, zero or more characters | `test_*` | `test_initialize` |
| `?` | Matches any single character | `???_test` | `obj_test`, `spr_test` |
| `[string]` | Matches exactly one character that is a member of the string string. This is called a character class. As a shorthand, string may contain ranges, which consist of two characters with a dash between them. For example, the class `[a-z0-9_]` matches a lowercase letter, a number, or an underscore. You can negate a class by placing a `!` or `^` immediately after the opening bracket. Thus, `[^A-Z@]` matches any character except an uppercase letter or an at sign. | `obj_gr[ae]y` | `obj_grey`, `obj_gray` |
| `\` | Escape character. Removes the special meaning of the character following it | `group name\?` | `group name?` |

Example:
```json
{
    "name": "dukesoft/other-project",
    "description": "Other project",
    "license": "MIT",
    "homepage": "https://github.com/robquistnl/other",
    "yyp": "OtherProject.yyp",
    "ignore": {
        "spr_to_ignore": "resource",
        "group_to_ignore": "group",
        "test_*": "all",
        "*_test2": "resource",
        "*_test3": "group"
    }
}
``` 

When installing this package, everything matching the rules in the `ignore` part, will not be installed into the package.

### Arguments

`catalyst help` will display all commands and information

| Argument | Options | Explaination | Example |
|----------|---------|--------------|---------|
| -h, --help | | Shows help | `catalyst -h` |
| -q, --quiet| | Do not output any message | `catalyst -q` |
| -V, --version| | Display this application version | `catalyst --version` |
| --ansi| | Force ANSI output | `catalyst --ansi` |
| --no-ansi| | Disable ANSI output | `catalyst --no-ansi` |
| -n, --no-interaction| | Do not ask any interactive question | `catalyst -n` |
| -v, -vv, -vvv, --verbose| | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug | `catalyst -vvv` |
|----------|---------|--------------|---------|
| clean | | Remove all installed dependencies | `catalyst clean` |
| help | | Displays help for a command | `catalyst help install` |
| init | | Initialize a project in the current folder | `catalyst init` |
| install | | Install all dependencies | `catalyst install` |
| require | | Require a package | `catalyst require gamemakerhub/extended-functions@^1.0` |
| tree | | Outputs a tree view of all assets in the project | `catalyst tree` |
| resources | | Lists all the resources in the project | `catalyst resources` |
