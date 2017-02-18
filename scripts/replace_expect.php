#!/usr/bin/env php
<?php
/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

$tests_dir = realpath(__DIR__ . '/../tests');
$iterator = new GlobIterator($tests_dir . '/*.out', FilesystemIterator::KEY_AS_FILENAME);

foreach ($iterator as $item) {
    //var_dump($item);
    $out_file = $iterator->key();
    $base_name = preg_replace('/\.out$/i', '', $iterator->key());
    $test_file = $base_name .'.phpt';

    $test_content = file_get_contents($tests_dir . '/' . $test_file);

    if (false !== ($pos = strpos($test_content, '--EXPECT--'))) {
        printf("--EXPECT--  [%s]".PHP_EOL, $iterator->key());

        $test_content = substr($test_content, 0, $pos);
        $test_content .= '--EXPECT--'.PHP_EOL;
        $test_content .= file_get_contents($tests_dir . '/' . $out_file);
        $test_content .= PHP_EOL;
        file_put_contents($tests_dir . '/' . $test_file, $test_content);

        foreach (['.diff', '.exp', '.log', '.mem', '.out', '.php', '.sh'] as $ext) {
            @unlink($tests_dir. '/'.$base_name . $ext);
        }
    } else {
        printf("please, edit manually [%s]".PHP_EOL, $iterator->key());
    }
}
