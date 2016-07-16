#!/bin/bash

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++11 \
 -I/usr/ \
 -I/usr/include \
 -L/usr/lib/ \
 -lv8_libbase \
 -lv8_libplatform \
 -lv8 \
 -lpthread
