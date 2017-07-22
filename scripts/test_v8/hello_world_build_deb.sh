#!/bin/bash

ROOT=/opt/libv8-6.2
LIB_DIR=$ROOT/lib/

SRC_DIR=$ROOT
INCLUDE_DIR=$ROOT/include

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++11 \
 -I$SRC_DIR \
 -I$INCLUDE_DIR \
 -L$LIB_DIR \
 -lv8_libbase \
 -lv8_libplatform \
 -lv8 \
 -lpthread
