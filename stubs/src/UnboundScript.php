<?php


namespace V8;

/**
 * A compiled JavaScript script, not yet tied to a Context.
 */
class UnboundScript
{
    const kNoScriptId = 0;

    private function __construct()
    {
    }

    /**
     * Binds the script to the currently entered context.
     *
     * @param Context $context
     *
     * @return Script
     */
    public function BindToContext(Context $context): Script
    {
    }

    /**
     * @return int
     */
    public function GetId(): int
    {
    }

    /**
     * @return Value
     */
    public function GetScriptName(): Value
    {
    }

    /**
     * Data read from magic sourceURL comments.
     *
     * @return Value
     */
    public function GetSourceURL(): Value
    {
    }

    /**
     * Data read from magic sourceMappingURL comments.
     *
     * @return Value
     */
    public function GetSourceMappingURL(): Value
    {
    }

    /**
     * Returns zero based line number of the code_pos location in the script.
     * -1 will be returned if no information available.
     *
     * @param int $code_pos
     *
     * @return int
     */
    public function GetLineNumber(int $code_pos): int
    {
    }
}
