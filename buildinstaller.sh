#!/bin/bash

mkdir dist/packed/windows/ -p

cp -r src dist/packed/windows/
cp -r bin dist/packed/windows/
cp -r config dist/packed/windows/

cp files/icon.ico dist/packed/windows/
cp index.php dist/packed/windows/
cp LICENSE dist/packed/windows/LICENSE.txt
cp README.md dist/packed/windows/README.txt
cp composer.json dist/packed/windows/
cp composer.lock dist/packed/windows/

cd dist/packed/windows/
../../../composer.phar install --no-dev --optimize-autoloader --prefer-dist
rm composer.json
rm composer.lock
echo Removing test files...
cd vendor
find . -name tests -type d -print0|xargs -0 rm -r --
find . -name Tests -type d -print0|xargs -0 rm -r --
find . -name Test -type d -print0|xargs -0 rm -r --
find . -name test -type d -print0|xargs -0 rm -r --
find . -name .git -type d -print0|xargs -0 rm -r --
find . -name .github -type d -print0|xargs -0 rm -r --
find . -name LICENSE -print0|xargs -0 rm -r --
find . -name composer.json -print0|xargs -0 rm -r --
find . -name README.md -print0|xargs -0 rm -r --
find . -name CHANGELOG.md -print0|xargs -0 rm -r --
find . -name .gitignore -print0|xargs -0 rm -r --
find . -name phpunit.xml.dist -print0|xargs -0 rm -r --
cd ../../../../

mkdir dist/packed/windows/dist/php7.3/ext -p
cp C:/php7.3/php.exe dist/packed/windows/dist/php7.3/
cp C:/php7.3/php7.dll dist/packed/windows/dist/php7.3/
cp files/php.ini dist/packed/windows/dist/php7.3/

cp C:/php7.3/ext/php_mbstring.dll dist/packed/windows/dist/php7.3/ext/
cp C:/php7.3/ext/php_curl.dll dist/packed/windows/dist/php7.3/ext/
cp C:/php7.3/ext/php_openssl.dll dist/packed/windows/dist/php7.3/ext/
cp C:/php7.3/ext/php_sockets.dll dist/packed/windows/dist/php7.3/ext/

cp C:/php7.3/cacert.pem dist/packed/windows/dist/php7.3/

cp C:/php7.3/lib*.dll dist/packed/windows/dist/php7.3/
cp C:/php7.3/nghttp2.dll dist/packed/windows/dist/php7.3/

"C:\Program Files (x86)\NSIS\makensis.exe" installer.nsi