#!/usr/bin/env php
<?php

chdir(__DIR__ . DIRECTORY_SEPARATOR . '..');

$contents = [
    'src'        => 'src',
    'stubs'      => 'doc',
    'tests'      => 'test',
    'config.m4'  => 'src',
    'config.w32' => 'src',
    'LICENSE'    => 'doc',
    'php_v8.h'   => 'src',
    'README.md'  => 'doc',
    'v8.cc'      => 'src',
];


$files = [];

$files[] = '<!-- begin files list -->';

foreach ($contents as $location => $role) {
    if (is_dir($location)) {

        /** @var SplFileInfo $filename */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($location)) as $filename) {
            if ($filename->isDir()) {
                continue;
            }

            $location = $filename->getPathname();

            if (!is_file($location)) {
                throw new Exception("'{$location}' is not a file");
            }

            $files[] = "            <file name=\"{$location}\" role=\"{$role}\" />";
        }

        continue;
    }

    if (!is_file($location)) {
        throw new Exception("'{$location}' is not a file");
    }

    $files[] = "            <file name=\"{$location}\" role=\"{$role}\" />";
}

$files[] = '            <!-- end files list -->';

$package = file_get_contents('package.xml');

$start = preg_quote('<!-- begin files list -->');
$end   = preg_quote('<!-- end files list -->');

$package = preg_replace("/{$start}.+{$end}/s", implode("\n", $files), $package);

file_put_contents('package-new.xml', $package);
