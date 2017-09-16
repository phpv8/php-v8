<?php declare(strict_types=1);

namespace V8\ScriptCompiler;

use V8\ScriptOrigin;
use V8\StringValue;


/**
 * Source code which can be then compiled to a UnboundScript or Script.
 */
class Source
{
    /**
     * @var StringValue
     */
    private $source_string;
    /**
     * @var null|ScriptOrigin
     */
    private $origin;
    /**
     * @var null|CachedData
     */
    private $cached_data;

    /**
     * @param StringValue       $source_string
     * @param ScriptOrigin|null $origin
     * @param CachedData|null   $cached_data
     */
    public function __construct(StringValue $source_string, ScriptOrigin $origin = null, CachedData $cached_data = null)
    {
        $this->source_string = $source_string;
        $this->origin        = $origin;
        $this->cached_data   = $cached_data;
    }

    public function getSourceString(): StringValue
    {
        return $this->source_string;
    }

    public function getScriptOrigin(): ScriptOrigin
    {
        return $this->origin;
    }

    public function getCachedData(): CachedData
    {
        return $this->cached_data;
    }
}
