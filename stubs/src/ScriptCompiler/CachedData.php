<?php


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

    // TODO: technically, we can use \strlen($this->getData()) when we need, though in PHP it's not necessary to get string length before fetching string itself
    //public function getLength(): int
    //{
    //}

    public function isRejected(): bool
    {
    }
}
