#!/usr/bin/env python

import os

parentdir = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
os.sys.path.insert(0, parentdir)

import deps

deps_loader = deps.V8DepsLocalFileLoader('./DEPS')
deps_content = deps_loader.load()

deps_resolver = deps.PPAPackagingDepsResolver(deps_content)

deps_resolver.import_deps_fast()
