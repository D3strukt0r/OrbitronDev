#!/bin/bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

CWD='/var/www/main'

echo -en "[....] Justifying ownership and permissions"

chown www-data:www-data -R $CWD
chmod u+rw -R $CWD
chmod g+rw -R $CWD
chmod o+r -R $CWD

echo -en "\r[ OK ]"

echo -en "\nPermissions fixed\n"
