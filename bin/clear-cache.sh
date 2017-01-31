#!/bin/bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

CWD='/var/www/main'

echo -en "[....] Enabling maintenance mode"
mv $CWD/web/.htaccess $CWD/web/.htaccess-original
mv $CWD/web/.htaccess-maintenance $CWD/web/.htaccess
echo -en "\r[ OK ]"

echo -en "\n[....] Justifying ownership and permissions"
chown www-data:www-data -R $CWD
chmod g+w -R $CWD
echo -en "\r[ OK ]"

echo -en "\n[....] Clearing cache for \"dev\""
php $CWD/bin/console cache:clear --quiet
echo -en "\r[ OK ]"

echo -en "\n[....] Clearing cache for \"prod\""
php $CWD/bin/console cache:clear --quiet --env=prod
echo -en "\r[ OK ]"

echo -en "\n[....] Justifying ownership and permissions"
chown www-data:www-data -R $CWD/
chmod g+w -R $CWD/
echo -en "\r[ OK ]"

echo -en "\n[....] Disabling maintenance mode"
mv $CWD/web/.htaccess $CWD/web/.htaccess-maintenance
mv $CWD/web/.htaccess-original $CWD/web/.htaccess
echo -en "\r[ OK ]"

echo -en "\nCache cleared\n"
