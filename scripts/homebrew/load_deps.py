#!/usr/bin/env python

import sys
import os
import argparse

parentdir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.sys.path.insert(0, parentdir)

import deps

parser = argparse.ArgumentParser()
parser.add_argument('--use-orig-repo', action='store_true', help='Use original repo rather then github mirror (use at your own risk)')
parser.add_argument('version', help='V8 version to build')
args = parser.parse_args()


deps_loader = deps.V8DepsRemoteFileLoader(args.version)
deps_content = deps_loader.load()

dir_path = os.path.dirname(os.path.realpath(__file__))

deps_resolver = deps.HomebrewDepsResolver(deps_content, args.version, dir_path +'/v8.rb.in', dir_path + '/v8.rb')

deps_resolver.import_deps_fast(args.use_orig_repo)
