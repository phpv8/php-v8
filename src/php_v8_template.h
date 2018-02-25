/*
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

#ifndef PHP_V8_TEMPLATE_H
#define PHP_V8_TEMPLATE_H

namespace phpv8 {
    class TemplateNode;
}

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

#include <set>

extern zend_class_entry* php_v8_template_ce;

extern void php_v8_object_template_Set(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_Set(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_object_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_object_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_object_template_SetLazyDataProperty(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_SetLazyDataProperty(INTERNAL_FUNCTION_PARAMETERS);

#define PHP_V8_TEMPLATE_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_template_ce, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_TEMPLATE_READ_ISOLATE(from_zval) zend_read_property(php_v8_template_ce, (from_zval), ZEND_STRL("isolate"), 0, &rv)

namespace phpv8 {
    class TemplateNode {
    public:
        std::set<TemplateNode *> children;
        std::set<TemplateNode *> parents;

        bool isSelf(TemplateNode *node) {
            return this == node;
        }

        bool isParent(TemplateNode *node) {
            if (parents.find(node) != parents.end()) {
                return true;
            }

            for (TemplateNode *parent : parents) {
                if (parent->isParent(node)) {
                    return true;
                }
            }

            return false;
        }

        //bool isChild(TemplateNode *node) {
        //    if (children.find(node) != children.end()) {
        //        return true;
        //    }
        //
        //    for (TemplateNode *child : children) {
        //        if (child->isChild(node)) {
        //            return true;
        //        }
        //    }
        //
        //    return false;
        //}

        void addChild(TemplateNode *node) {
            children.insert(node);
            node->parents.insert(this);
        }

        ~TemplateNode() {
            for (TemplateNode *parent : parents) {
                parent->children.erase(this);
            }

            for (TemplateNode *child : children) {
                child->parents.erase(this);
            }
        }
    };
}


PHP_MINIT_FUNCTION(php_v8_template);

#endif //PHP_V8_TEMPLATE_H
