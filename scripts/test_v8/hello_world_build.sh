#!/bin/bash

g++ hello_world.cpp -o hello_world \
 -g \
 -O2 \
 -std=c++11 \
 -Wl,--rpath=/home/vagrant/Development/php-v8/tmp/usr/lib/v8/lib \
 -I/home/vagrant/Development/php-v8/tmp/usr/lib/v8 \
 -I/home/vagrant/Development/php-v8/tmp/usr/lib/v8/include \
 /home/vagrant/Development/php-v8/tmp/usr/lib/v8/lib/libv8_libplatform.a \
 -L/home/vagrant/Development/php-v8/tmp/usr/lib/v8/lib \
 -lv8 \
 -lpthread
