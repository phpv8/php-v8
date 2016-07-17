<?php

/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/


namespace V8\StackTrace;

/**
 * Flags that determine what information is placed captured for each
 * StackFrame when grabbing the current stack trace.
 */
class StackTraceOptions
{
    const kLineNumber = 1;
    const kColumnOffset = 1 << 1 | self::kLineNumber;
    const kScriptName = 1 << 2;
    const kFunctionName = 1 << 3;
    const kIsEval = 1 << 4;
    const kIsConstructor = 1 << 5;
    const kScriptNameOrSourceURL = 1 << 6;
    const kScriptId = 1 << 7;
    const kExposeFramesAcrossSecurityOrigins = 1 << 8;
    const kOverview = self::kLineNumber | self::kColumnOffset | self::kScriptName | self::kFunctionName;
    const kDetailed = self::kOverview | self::kIsEval | self::kIsConstructor | self::kScriptNameOrSourceURL;
}
