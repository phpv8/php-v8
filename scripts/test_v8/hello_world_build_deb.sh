#!/bin/bash

ROOT=/opt/libv8-6.5
LIB_DIR=$ROOT/lib/

SRC_DIR=$ROOT
INCLUDE_DIR=$ROOT/include

set -x

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++14 \
 -I$INCLUDE_DIR \
 -L$LIB_DIR \
 -Wl,-rpath,$LIB_DIR \
 -lv8_libbase \
 -lv8_libplatform \
 -lv8 \
 -lpthread
