
# Catalyst
> A Game Maker Studio 2 dependency manager.

#### PLEASE NOTE THAT THIS IS STILL IN DEVELOPMENT AND MIGHT BREAK YOUR PROJECT - MAKE SURE YOU HAVE BACKUPS / VCS

[![Build Status](https://travis-ci.org/GameMakerHub/Catalyst.svg?branch=master)](https://travis-ci.org/GameMakerHub/Catalyst)
[![Coverage](https://codecov.io/gh/GameMakerHub/Catalyst/branch/master/graph/badge.svg)](https://codecov.io/gh/GameMakerHub/Catalyst)
[![HitCount](http://hits.dwyl.io/GameMakerHub/Catalyst.svg)](http://hits.dwyl.io/GameMakerHub/Catalyst)

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

`catalyst help` will display all commands and information

### Arguments

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
