#!/usr/bin/env python

import sys
import os
import urllib
import hashlib
import argparse


parser = argparse.ArgumentParser()
parser.add_argument('--libv8-version', help='Specify required libv8 formula dependency')
parser.add_argument('version', help='php-v8 version to generate formulas for')
args = parser.parse_args()


class HomebrewPhpV8(object):
    def __init__(self, tpl_path, out_path):
        self.tpl_path = tpl_path
        self.out_path = out_path

    def generate(self, version, libv8_version, php_versions):
        vars = {}

        url = "https://github.com/pinepain/php-v8/archive/v%s.tar.gz" % version

        f = urllib.urlopen(url)
        s = f.read()
        f.close()

        sha256 = hashlib.sha256(s).hexdigest()

        vars['{{ URL }}'] = 'url "%s"' % url
        vars['{{ SHA256 }}'] = 'sha256 "%s"' % sha256

        if libv8_version:
            vars['{{ LIBV8_DEPENDENCY }}'] = 'depends_on "libv8-%s"' % libv8_version
        else:
            vars['{{ LIBV8_DEPENDENCY }}'] = '# NOTE: This formula depends on libv8, but actual "depends_on" dependency is not set yet'

        for php in php_versions:
            vars['{{ PHP_VERSION }}'] = php

            tpl = ""
            with open(self.tpl_path) as f:
                tpl = f.read()

            for k, v in vars.iteritems():
                tpl = tpl.replace(k, v)

            out_path = self.out_path
            for k, v in vars.iteritems():
                out_path = out_path.replace(k, v)

            with open(out_path, 'w') as f:
                f.write(tpl)


dir_path = os.path.dirname(os.path.realpath(__file__))

deps_resolver = HomebrewPhpV8(dir_path +'/php-v8.rb.in', dir_path + '/php{{ PHP_VERSION }}-v8.rb')
deps_resolver.generate(args.version, args.libv8_version, ['70', '71'])
