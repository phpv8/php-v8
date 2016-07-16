#!/bin/bash

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++11 \
 -I/usr/local \
 -I/usr/local/include \
 -L/usr/local/lib/ \
 -lv8_libbase \
 -lv8_libplatform \
 -lv8 \
 -lpthread
