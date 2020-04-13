#!/bin/bash
  if [ ! $# -eq 1 ]; then
    echo "Error: missing version parameter"
    exit 1
  else
    if [[ $1 =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
      echo "Bumping version to $1"
    else
      echo "$1: Not a valid version number"
      exit 1
    fi
  fi

sed -i -E -e "s/'[0-9]+\.[0-9]+\.[0-9]+'/'$1'/g" index.php
sed -i -E -e "s/\"[0-9]+\.[0-9]+\.[0-9]\"+/\"$1\"/g" installer.nsi
echo "Now making commit for version bump... Adding everything and tagging! Make sure you're on master branch"
pause
git add .
git commit -m "Bump version to $1"
git tag
git push origin --tags