
# Table of contents:
 - [Setup](#setup)
   - [Windows](#windows)
   - [Linux and OSX](#linux-and-osx)
 - [Usage](#usage)
   - [Arguments](#arguments)


## Setup

### Windows
You want to install PHP7.3 (older 7 will also work) and you might want to install a tool like Git Bash (that also has 
MINGW). This way you have a nice Linux-style CLI which you can work with.

If the command "php -v" doesn't work (can't find PHP) open up Git Bash as an admin (e.g. start -> cmd -> right mouse 
button -> open as administrator), and create the file "/usr/bin/php" with following contents:
```sh
#!/bin/bash
/c/php7.3/php.exe ${@:1:99}
```

where /c/php7.3/php.exe is your PHP's location ofcourse.

You also need to make sure you have added the following lines to the php.ini if they don't yet exist:
```ini
extension=php_gd2.dll
extension=php_curl.dll
extension=php_mbstring.dll
extension=php_openssl.dll
extension=php_sockets.dll
```

You also need to install composer (or download the .phar file into this directory), and then run `composer install` (or 
`php ./composer.phar install` if you dont have it installed)

### Linux and OSX

Make sure you have PHP7.3 and Composer installed.

Run a `composer install` in this directory, and then you can use it.

## Usage

`./index.php -h` will display help. If you can't link the PHP executable you'll have to manually call it like so;
`/c/php7.3/php.exe index.php -h`

### Arguments

| Argument | Options | Explaination | Example |
|----------|-------------|------|------|
| -h, --help | | Shows help | `./index.php -h` |
