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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <libplatform/libplatform.h>

#include "php_v8_a.h"
#include "php_v8.h"
#include <v8.h>

void php_v8_init()
{
    /* Run only once */
    if (PHP_V8_G(v8_initialized)) {
        return;
    }

    v8::V8::InitializeICUDefaultLocation(PHP_V8_LIB_DIR);

    // NOTE: if we use snapshot and extenal startup data then we have to initialize it (see https://codereview.chromium.org/315033002/)
    // v8::V8::InitializeExternalStartupData(NULL);
    v8::Platform *platform = v8::platform::CreateDefaultPlatform();
    v8::V8::InitializePlatform(platform);

//    const char *flags = "--no-hard_abort";
//    v8::V8::SetFlagsFromString(flags, strlen(flags));


    // TODO: remove flags?
    /* Set V8 command line flags (must be done before V8::Initialize()!) */
//    if (PHP_V8_G(v8_flags)) {
//        v8::V8::SetFlagsFromString(PHP_V8_G(v8_flags), strlen(PHP_V8_G(v8_flags)));
//    }

    /* Initialize V8 */
    v8::V8::Initialize();

    /* Run only once */
    PHP_V8_G(v8_initialized) = true;

//    TODO: probably, not necessary to call it on shutdown
//    v8::V8::Dispose();
//    v8::V8::ShutdownPlatform();
}
