#!/usr/bin/env bash

#set -e

export WORKON_HOME=$HOME/.virtualenvs
source /usr/local/bin/virtualenvwrapper.sh

cdir=$(pwd)

cd /Users/dmitry/Work/pytin/cmdb/
rm -f ./db/cmdb.sqlite3

workon pytin
./manage.py makemigrations
./manage.py migrate
deactivate

cd ${cdir}
