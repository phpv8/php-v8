#!/usr/bin/env python

import sys
import os

parentdir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.sys.path.insert(0, parentdir)

import deps

if len(sys.argv) != 2:
    # print("Usage: %s <path to DEPS file>" % sys.argv[0])
    print("Usage: %s <v8 version>" % sys.argv[0])
    exit(1)

version = sys.argv[1]

deps_loader = deps.V8DepsRemoteFileLoader(version)
deps_content = deps_loader.load()

dir_path = os.path.dirname(os.path.realpath(__file__))

deps_resolver = deps.HomebrewDepsResolver(deps_content, version, dir_path +'/v8.rb.in', dir_path + '/v8.rb')

deps_resolver.import_deps_fast()
