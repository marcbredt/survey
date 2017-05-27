#!/bin/bash
# simple deploy script for the magix-survey

if [ "$(whoami)" != "root" ]; then echo "E: You need be root."; exit 1; fi
cd ${0%/*}

rm -rf log/ deploy/
mkdir log/ deploy/
cp -Rf conf/ core/ main/ deploy/
find deploy/ -type d -exec chmod 750 {} +
find deploy/ -type f -exec chmod 660 {} +
find deploy/ -exec chown www-data:www-data {} +
find log/ -type d -exec chmod 750 {} +
find log/ -exec chown www-data:www-data {} +

cd -
