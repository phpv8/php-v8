#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"

cd $DIR

if [ -d ".subsplit" ]; then
    git subsplit update
else
    git subsplit init .
fi

git subsplit publish --heads="master" stubs:git@github.com:pinepain/php-v8-stubs.git
