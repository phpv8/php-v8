PHP_ARG_WITH(v8, for V8 Javascript Engine,
[  --with-v8               Include V8 JavaScript Engine])

if test "$PHP_V8" != "no"; then
  SEARCH_PATH="/usr/local /usr"
  SEARCH_FOR="include/v8.h"

  if test -r $PHP_V8/$SEARCH_FOR; then
    case $host_os in
      darwin* )
        # MacOS does not support --rpath
        ;;
      * )
        LDFLAGS="$LDFLAGS -Wl,--rpath=$PHP_V8/$PHP_LIBDIR"
        ;;
    esac
    V8_DIR=$PHP_V8
  else
    AC_MSG_CHECKING([for V8 files in default path])
    for i in $SEARCH_PATH ; do
      if test -r $i/$SEARCH_FOR; then
        V8_DIR=$i
        AC_MSG_RESULT(found in $i)
      fi
    done
  fi

  if test -z "$V8_DIR"; then
    AC_MSG_RESULT([not found])
    AC_MSG_ERROR([Please reinstall the v8 distribution])
  fi

  PHP_ADD_INCLUDE($V8_DIR/include)
  PHP_ADD_LIBRARY_WITH_PATH(v8, $V8_DIR/$PHP_LIBDIR, V8_SHARED_LIBADD)
  PHP_SUBST(V8_SHARED_LIBADD)
  PHP_REQUIRE_CXX()


  AC_CACHE_CHECK(for C standard version, ac_cv_v8_cstd, [
    ac_cv_v8_cstd="c++11"
    old_CPPFLAGS=$CPPFLAGS
    AC_LANG_PUSH([C++])
    CPPFLAGS="-std="$ac_cv_v8_cstd
    AC_TRY_RUN([int main() { return 0; }],[],[ac_cv_v8_cstd="c++0x"],[])
    AC_LANG_POP([C++])
    CPPFLAGS=$old_CPPFLAGS
  ]);


  old_LIBS=$LIBS
  old_LDFLAGS=$LDFLAGS
  old_CPPFLAGS=$CPPFLAGS

  case $host_os in
    darwin* )
      # MacOS does not support --rpath
      LDFLAGS="-L$V8_DIR/$PHP_LIBDIR"
      ;;
    * )
      LDFLAGS="-Wl,--rpath=$V8_DIR/$PHP_LIBDIR -L$V8_DIR/$PHP_LIBDIR"
      ;;
  esac

  PHP_ADD_INCLUDE($V8_DIR)

  case $host_os in
    darwin* )
      static_link_extra="libv8_libplatform.a libv8_libbase.a"
      #static_link_extra="libv8_base.a libv8_libbase.a libv8_libplatform.a libv8_snapshot.a"
      ;;
    * )
      static_link_extra="libv8_libplatform.a"
      #static_link_extra="libv8_base.a libv8_libbase.a libv8_libplatform.a libv8_snapshot.a"
      ;;
  esac

  for static_link_extra_file in $static_link_extra; do
    AC_MSG_CHECKING([for $static_link_extra_file])

    for i in $PHP_V8 $SEARCH_PATH ; do
      if test -r $i/lib64/$static_link_extra_file; then
        static_link_dir=$i/lib64
        AC_MSG_RESULT(found in $i)
      fi
      if test -r $i/lib/$static_link_extra_file; then
        static_link_dir=$i/lib
        AC_MSG_RESULT(found in $i)
      fi
    done

    if test -z "$static_link_dir"; then
      AC_MSG_RESULT([not found])
      AC_MSG_ERROR([Please provide $static_link_extra_file next to the libv8.so, see README.md for details])
    fi

    LDFLAGS="$LDFLAGS $static_link_dir/$static_link_extra_file"
  done

  LIBS=-lv8
  CPPFLAGS="-I$V8_DIR/include -std=$ac_cv_v8_cstd"
  AC_LANG_SAVE
  AC_LANG_CPLUSPLUS


  AC_CACHE_CHECK(for V8 version, ac_cv_v8_version, [
    AC_TRY_RUN([
      #include <v8.h>
      #include <iostream>
      #include <fstream>
      using namespace std;

      int main ()
      {
          ofstream testfile ("conftestval");
          if (testfile.is_open()) {
              testfile << v8::V8::GetVersion();
              testfile << "\n";
              testfile.close();
              return 0;
          }
          return 1;
      }
    ], [ac_cv_v8_version=`cat ./conftestval|awk '{print $1}'`], [ac_cv_v8_version=NONE], [ac_cv_v8_version=NONE])
  ])

  if test "$ac_cv_v8_version" != "NONE"; then
    ac_IFS=$IFS
    IFS=.
    set $ac_cv_v8_version
    IFS=$ac_IFS
    V8_API_VERSION=`expr [$]1 \* 1000000 + [$]2 \* 1000 + [$]3`
    if test "$V8_API_VERSION" -lt 501000 ; then
       AC_MSG_ERROR([libv8 must be version 5.1.0 or greater])
    fi
    AC_DEFINE_UNQUOTED([PHP_LIBV8_API_VERSION], $V8_API_VERSION, [ ])
    AC_DEFINE_UNQUOTED([PHP_LIBV8_VERSION], "$ac_cv_v8_version", [ ])
  else
    AC_MSG_ERROR([could not determine libv8 version])
  fi

  # On OS X clang reports warnings in zeng_strings.h, like
  #     php/Zend/zend_string.h:326:2: warning: 'register' storage class specifier is deprecated [-Wdeprecated-register]
  # We want to make building log cleaner, so let's suppress this warning
  ac_cv_suppress_register_warnings_flag="-Wno-deprecated-register"

  AC_DEFINE([V8_DEPRECATION_WARNINGS], [1], [Enable compiler warnings when using V8_DEPRECATED apis.])

  AC_LANG_RESTORE
  LIBS=$old_LIBS
  #LDFLAGS=$old_LDFLAGS # we have to links some static libraries
  CPPFLAGS=$old_CPPFLAGS

  if test -z "$TRAVIS" ; then
    type git &>/dev/null

    if test $? -eq 0 ; then
      git describe --abbrev=0 --tags &>/dev/null

      if test $? -eq 0 ; then
        AC_DEFINE_UNQUOTED([PHP_V8_VERSION], ["`git describe --abbrev=0 --tags`-`git rev-parse --abbrev-ref HEAD`-dev"], [git version])
      fi

      git rev-parse --short HEAD &>/dev/null

      if test $? -eq 0 ; then
        AC_DEFINE_UNQUOTED([PHP_V8_REVISION], ["`git rev-parse --short HEAD`"], [git revision])
      fi
    else
      AC_MSG_NOTICE([git not installed. Cannot obtain php-weak version tag. Install git.])
    fi
  fi

  PHP_V8_SRCDIR=PHP_EXT_SRCDIR(v8)
  PHP_V8_BUILDDIR=PHP_EXT_BUILDDIR(v8)

  PHP_ADD_INCLUDE($PHP_V8_SRCDIR/src)
  PHP_ADD_BUILD_DIR($PHP_V8_BUILDDIR/src)

  PHP_NEW_EXTENSION(v8, [                                 \
    v8.cc                                                 \
    src/php_v8_a.cc                                       \
    src/php_v8_exception.cc                               \
    src/php_v8_try_catch.cc                               \
    src/php_v8_message.cc                                 \
    src/php_v8_stack_frame.cc                             \
    src/php_v8_stack_trace.cc                             \
    src/php_v8_script_origin_options.cc                   \
    src/php_v8_script_origin.cc                           \
    src/php_v8_exceptions.cc                              \
    src/php_v8_callbacks.cc                               \
    src/php_v8_startup_data.cc                            \
    src/php_v8_heap_statistics.cc                         \
    src/php_v8_isolate.cc                                 \
    src/php_v8_isolate_limits.cc                          \
    src/php_v8_context.cc                                 \
    src/php_v8_object_template.cc                         \
    src/php_v8_function_template.cc                       \
    src/php_v8_script.cc                                  \
    src/php_v8_data.cc                                    \
    src/php_v8_value.cc                                   \
    src/php_v8_primitive.cc                               \
    src/php_v8_null.cc                                    \
    src/php_v8_boolean.cc                                 \
    src/php_v8_name.cc                                    \
    src/php_v8_string.cc                                  \
    src/php_v8_symbol.cc                                  \
    src/php_v8_number.cc                                  \
    src/php_v8_integer.cc                                 \
    src/php_v8_int32.cc                                   \
    src/php_v8_uint32.cc                                  \
    src/php_v8_object.cc                                  \
    src/php_v8_function.cc                                \
    src/php_v8_array.cc                                   \
    src/php_v8_date.cc                                    \
    src/php_v8_regexp.cc                                  \
    src/php_v8_number_object.cc                           \
    src/php_v8_boolean_object.cc                          \
    src/php_v8_string_object.cc                           \
    src/php_v8_symbol_object.cc                           \
    src/php_v8_property_attribute.cc                      \
    src/php_v8_template.cc                                \
    src/php_v8_return_value.cc                            \
    src/php_v8_callback_info.cc                           \
    src/php_v8_function_callback_info.cc                  \
    src/php_v8_property_callback_info.cc                  \
    src/php_v8_access_control.cc                          \
    src/php_v8_property_handler_flags.cc                  \
    src/php_v8_named_property_handler_configuration.cc    \
    src/php_v8_indexed_property_handler_configuration.cc  \
    src/php_v8_access_type.cc                             \
  ], $ext_shared, , "$ac_cv_suppress_register_warnings_flag -std="$ac_cv_v8_cstd -DZEND_ENABLE_STATIC_TSRMLS_CACHE=1)

  PHP_ADD_BUILD_DIR($ext_builddir/src)

  PHP_ADD_MAKEFILE_FRAGMENT
fi
