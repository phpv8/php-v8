<?php declare(strict_types=1);


namespace V8\ScriptCompiler;


class CompileOptions
{
    const NO_COMPILE_OPTIONS   = 0;
    const PRODUCE_PARSER_CACHE = 1;
    const CONSUME_PARSER_CACHE = 2;
    const PRODUCE_CODE_CACHE   = 3;
    const CONSUME_CODE_CACHE   = 4;
}
