cmake_minimum_required(VERSION 3.1)
project(php_v8)

# NOTE: This CMake file is just for syntax highlighting in CLion

include_directories(/usr/local/opt/v8@5.9/include)
include_directories(/usr/local/opt/v8@5.9/include/libplatform)

include_directories(/usr/local/include/php)
include_directories(/usr/local/include/php/TSRM)
include_directories(/usr/local/include/php/Zend)
include_directories(/usr/local/include/php/ext)
include_directories(/usr/local/include/php/main)
include_directories(/usr/local/include/php/sapi)

add_definitions(-DV8_DEPRECATION_WARNINGS)
add_definitions(-DV8_IMMINENT_DEPRECATION_WARNINGS)

if(EXISTS "${CMAKE_SOURCE_DIR}/config.h")
    add_definitions(-DHAVE_CONFIG_H)
endif()

set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -std=c++11")

include_directories(./)
include_directories(./src)

FILE(GLOB SRC src/*.cc)
FILE(GLOB HDR src/*.h)

set(SOURCE_FILES
    ${HDR}
    ${SRC}

    php_v8.h
    v8.cc
)


add_executable(php_v8 ${SOURCE_FILES})
