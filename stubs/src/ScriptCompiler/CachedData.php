<?php declare(strict_types=1);

/**
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace V8\ScriptCompiler;


/**
 * Compilation data that the embedder can cache and pass back to speed up
 * future compilations. The data is produced if the CompilerOptions passed to
 * the compilation functions in ScriptCompiler contains produce_data_to_cache
 * = true. The data to cache can then can be retrieved from
 * UnboundScript.
 */
class CachedData
{
    public function __construct(string $data)
    {
    }

    public function getData(): string
    {
    }

    public function isRejected(): bool
    {
    }
}
