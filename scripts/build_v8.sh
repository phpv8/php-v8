#!/bin/bash

if [[ -z $1 ]]; then
    echo "Download and pack v8 and it deps for further debian/ubuntu packaging"
    echo "Usage: $1 <v8 version>"
    exit 1
fi

DIR=`dirname $0`
V8_VERSION=$1

$DIR/load_deps.py $V8_VERSION | xargs -L 1 sh -c 'echo "$0 => $1"; mkdir -p $1; curl -s $0 | tar zxf - -C $1'
tar -czf v8-$V8_VERSION.orig.tar.gz v8
