#!/usr/bin/env python

import sys
import urllib
import base64


class VarImpl(object):
    """
    This class taken from gclient.py from depot tools:
    # Copyright (c) 2012 The Chromium Authors. All rights reserved.
    # Use of this source code is governed by a BSD-style license that can be
    # found in the LICENSE file.

    // Copyright (c) 2009 The Chromium Authors. All rights reserved.
    //
    // Redistribution and use in source and binary forms, with or without
    // modification, are permitted provided that the following conditions are
    // met:
    //
    //    * Redistributions of source code must retain the above copyright
    // notice, this list of conditions and the following disclaimer.
    //    * Redistributions in binary form must reproduce the above
    // copyright notice, this list of conditions and the following disclaimer
    // in the documentation and/or other materials provided with the
    // distribution.
    //    * Neither the name of Google Inc. nor the names of its
    // contributors may be used to endorse or promote products derived from
    // this software without specific prior written permission.
    //
    // THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
    // "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
    // LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
    // A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
    // OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
    // SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
    // LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
    // DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
    // THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    // (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
    // OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
    """

    def __init__(self, custom_vars, local_scope):
        self._custom_vars = custom_vars
        self._local_scope = local_scope

    def Lookup(self, var_name):
        """Implements the Var syntax."""
        if var_name in self._custom_vars:
            return self._custom_vars[var_name]
        elif var_name in self._local_scope.get("vars", {}):
            return self._local_scope["vars"][var_name]
        raise gclient_utils.Error("Var is not defined: %s" % var_name)


def FileRead(filename, mode='rU'):
    with open(filename, mode=mode) as f:
        return f.read()


if len(sys.argv) < 2 or len(sys.argv) > 3:
    # print("Usage: %s <path to DEPS file>" % sys.argv[0])
    print("Usage: %s <v8 version>" % sys.argv[0])
    print("Options:")
    print("    --homebrew    Generate hombrew output")
    exit(1)


# https://chromium.googlesource.com/v8/v8.git/+archive/5.0.71.11.tar.gz
# https://chromium.googlesource.com/v8/v8.git/+/5.0.71.11/DEPS?format=TEXT


class V8DepsFileLoader(object):
    def __init__(self, version):
        self.version = version

    def load(self):
        url = 'https://chromium.googlesource.com/v8/v8.git/+/%s/DEPS?format=TEXT' % self.version
        f = urllib.urlopen(url)
        s = f.read()
        f.close()

        return base64.b64decode(s)


class V8DepsResolver(object):
    def __init__(self, version):
        self.version = version

    def get_deps(self):
        url = 'https://chromium.googlesource.com/v8/v8.git/+archive/%s.tar.gz' % self.version

        return {url: 'v8'}

    def output_deps(self):
        for k, v in self.get_deps().items():
            print ("%s %s" % (k, v))


class DepsResolver(object):
    def __init__(self, deps_content):
        self.deps_content = deps_content

    def parse_deps(self):
        custom_vars = {}
        local_scope = {}

        var = VarImpl(custom_vars, local_scope)

        global_scope = {
            '__builtins__': {'None': None},
            'Var': var.Lookup,
            'deps_os': {},
        }

        exec (self.deps_content, global_scope, local_scope)

        assert (local_scope.has_key('deps'))

        return local_scope['deps']

    def convert_parsed_deps(self, deps):
        deps_converted = {}

        for k, v in deps.items():
            v = v.replace('@', '/+archive/') + '.tar.gz'

            deps_converted[v] = k

        return deps_converted

    def get_deps(self):
        parsed = self.parse_deps()

        return self.convert_parsed_deps(parsed)

    def output_deps(self):
        for k, v in self.get_deps().items():
            print ("%s %s" % (k, v))


class HomebrewDepsResolver(DepsResolver):
    def convert_parsed_deps(self, deps):
        deps_converted = {}

        for k, v in deps.items():
            url, revision = v.split('@', 2)

            resource = url.rsplit('/', 1)[1].split('.', 1)[0]
            target = k.replace('v8/', '')

            deps_converted[resource] = {
                'url': url,
                'revision': revision,
                'resource': resource,
                'target': target
            }

        return deps_converted

    def output_deps(self):
        print ("  # resources definition, do not edit, autogenerated")
        print ("")

        for k, v in self.get_deps().items():
            print ("  resource \"%s\" do " % (v['resource']))
            print ("    url \"%s\"," % (v['url']))
            print ("    :revision => \"%s\"" % (v['revision']))
            print ("  end")
            print ("")

        print ("")
        print ("")
        print ("    # resources installation, do not edit, autogenerated")

        for k, v in self.get_deps().items():
            print ("    (buildpath/\"{target}\").install resource(\"{resource}\")".format(**v))

        print ("")

if len(sys.argv) == 2:
    version = sys.argv[1]
    is_homebrew = False
else:
    version = sys.argv[2]
    is_homebrew = True

deps_loader = V8DepsFileLoader(version)

deps_content = deps_loader.load()

# print(deps_content)
# deps_content = FileRead(sys.argv[1])

v8_deps_resolver = V8DepsResolver(version)
if is_homebrew:
    deps_resolver = HomebrewDepsResolver(deps_content)
else:
    deps_resolver = DepsResolver(deps_content)

v8_deps_resolver.output_deps()

deps_resolver.output_deps()
