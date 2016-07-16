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


namespace v8;


class StartupData
{
    /**
     * @param string $blob
     */
    public function __construct(string $blob)
    {
    }

    public function GetData() : string
    {
    }

    public function GetRawSize() : int
    {
    }

    /**
     * Runs v8::V8::CreateSnapshotDataBlob
     *
     * @param string $source
     *
     * @return StartupData
     */
    public static function CreateFromSource(string $source) : StartupData
    {
        $blob = '/* convert source to blob*/';

        return new self($blob);
    }
}