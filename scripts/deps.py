#!/usr/bin/env python

import sys
import os
import urllib
import base64
import hashlib

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


# https://chromium.googlesource.com/v8/v8.git/+archive/5.0.71.11.tar.gz
# https://chromium.googlesource.com/v8/v8.git/+/5.0.71.11/DEPS?format=TEXT

class V8DepsRemoteFileLoader(object):
    def __init__(self, version):
        self.version = version

    def load(self):
        url = 'https://chromium.googlesource.com/v8/v8.git/+/%s/DEPS?format=TEXT' % self.version
        f = urllib.urlopen(url)
        s = f.read()
        f.close()

        return base64.b64decode(s)

class V8DepsLocalFileLoader(object):
    def __init__(self, path):
        self.path = path

    def load(self):
        with open(self.path) as f:
            return f.read()


class AbstractDepsResolver(object):
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

    def get_deps(self):
        parsed = self.parse_deps()

        return self.convert_parsed_deps(parsed)


class HomebrewDepsResolver(AbstractDepsResolver):
    def __init__(self, deps_content, version, tpl_path, out_path):
        self.deps_content = deps_content
        self.version = version
        self.tpl_path = tpl_path
        self.out_path = out_path

    def import_deps_fast(self, use_orig_repo=False):
        vars = {}

        if use_orig_repo:
            url = "https://chromium.googlesource.com/v8/v8.git/+archive/%s.tar.gz" % self.version
            head = "https://chromium.googlesource.com/v8/v8.git"
        else:
            url = "https://github.com/v8/v8/archive/%s.tar.gz" % self.version
            head = "https://github.com/v8/v8.git"

        f = urllib.urlopen(url)
        s = f.read()
        f.close()

        sha256 = hashlib.sha256(s).hexdigest()

        vars['{{ URL }}'] = 'url "%s"' % url
        vars['{{ SHA256 }}'] = 'sha256 "%s"' % sha256
        vars['{{ HEAD }}'] = 'head "%s"' % head

        resources_def = []
        resources_def.append("  # resources definition, do not edit, autogenerated")
        resources_def.append("")

        for k, v in self.get_deps().items():
            resources_def.append("  resource \"%s\" do" % (v['resource']))
            resources_def.append("    url \"%s\"," % (v['url']))
            resources_def.append("    :revision => \"%s\"" % (v['revision']))
            resources_def.append("  end")
            resources_def.append("")

        vars['{{ RESOURCES_DEFINITION }}'] = "\n".join(resources_def).strip()

        resources_inst = []
        resources_inst.append("    # resources installation, do not edit, autogenerated")

        for k, v in self.get_deps().items():
            resources_inst.append("    (buildpath/\"{target}\").install resource(\"{resource}\")".format(**v))

        vars['{{ RESOURCES_INSTALLATION }}'] = "\n".join(resources_inst).strip()

        tpl = ""
        with open(self.tpl_path) as f:
            tpl = f.read()

        for k, v in vars.iteritems():
            tpl = tpl.replace(k, v)

        with open(self.out_path, 'w') as f:
            f.write(tpl)


class PPAPackagingDepsResolver(AbstractDepsResolver):
    def import_deps_fast(self):
        for k, v in self.get_deps().items():
            tgz = v['url'] + '/+archive/' +  v['revision']+ '.tar.gz'

            cmd = "mkdir -p " + v['target']

            if os.path.isdir(v['target']):
                cmd = "rm -rf " + v['target'] + " && " + cmd

            print(cmd)
            os.system(cmd)

            cmd = "curl -s " + tgz + " | tar zxf - -C " + v['target']
            print (cmd)
            os.system(cmd)
