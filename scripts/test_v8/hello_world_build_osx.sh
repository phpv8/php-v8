#!/bin/bash

ROOT=/usr/local/opt/v8@6.5
LIB_DIR=$ROOT/lib/

SRC_DIR=$ROOT
INCLUDE_DIR=$ROOT/include

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++14 \
 -I$SRC_DIR \
 -I$INCLUDE_DIR \
 -L$LIB_DIR \
 -lv8_libbase \
 -lv8_libplatform \
 -lv8 \
 -lpthread

install_name_tool -add_rpath $LIB_DIR hello_world
