<?php declare(strict_types=1);

/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace V8;


class StartupData
{
    /**
     * @param string $blob
     */
    public function __construct(string $blob)
    {
    }

    public function getData(): string
    {
    }

    public function isRejected(): bool
    {
    }

    /**
     * Runs v8::V8::CreateSnapshotDataBlob
     *
     * @param string $source
     *
     * @return StartupData
     */
    public static function createFromSource(string $source): StartupData
    {
        $blob = '/* convert source to blob*/';

        return new self($blob);
    }

    /**
     * Bootstrap an isolate and a context from the cold startup blob, run the
     * warm-up script to trigger code compilation. The side effects are then
     * discarded. The resulting startup snapshot will include compiled code.
     *
     * The argument startup blob is untouched.
     *
     * @param StartupData $cold_startup_data
     * @param string      $warmup_source
     *
     * @return StartupData
     */
    public static function warmUpSnapshotDataBlob(StartupData $cold_startup_data, string $warmup_source): StartupData
    {
    }
}
