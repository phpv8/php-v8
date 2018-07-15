**************
Release php-v8
**************

GitHub release
==============

#. Make sure current state is ready for release:

    - All relevant PR merged and issues closed.
    - Build passed.

#. Prepare release notes by creating release draft on github.
#. Update ``PHP_V8_VERSION`` to have desired version and set ``PHP_V8_REVISION`` to ``release`` in ``php_v8.h``.
#. Run ``./scripts/refresh-package-xml.php -f`` to update ``package.xml`` with proper ``php-v8`` version and update directories
   and files tree.
#. Update ``package.xml`` ``<notes>`` with release notes. Keep eye on special characters to properly escape them,
   e.g. ``>`` should be written as ``&gt;`` instead.
#. Commit all changes with ``Prepare X.Y.Z release`` commit message.
#. Push this commit and make sure it will pass the build.
#. Tag it with ``vX.Y.Z`` tag and push. Create github release from a draft prepared in step above.
#. Close relevant milestone, if any.
#. Run ``./scripts/subsplit.sh`` to update ``php-v8-stubs`` which are available in a separate read-only repository to match
    packagist and composer expectations. 

PECL release
============

#. Run ``pecl package`` in your build machine (it's normally vagrant box used for ``php-v8`` development). It should create
   ``v8-X.Y.Z.tgz`` file.
#. Log in to PECL and upload file from previous step at https://pecl.php.net/release-upload.php. Verify that release info
   is accurate and confirm release.

Docker image release
====================

#. Go into `pinepain/dockerfiles <https://github.com/pinepain/dockerfiles/tree/master/php-v8>`_ ``php-v8`` folder.
#. Make sure you have valid stable and latest versions in ``Makefile``.
#. To avoid caching, run ``make clean-stable`` to remove any image for the current stable version
   and ``make clean-latest`` to do the same for the current latest version.
#. Run ``make stable`` to build and upload current stable version
   and ``make latest`` to build and upload the latest version.

After all
=========

#. Update `js-sandbox`_ dependencies, if required, to use latest ``php-v8`` and other dependencies, if any.
#. Update ``PHP_V8_VERSION`` to the next version and set ``PHP_V8_REVISION`` to ``dev`` in ``php_v8.h``.
#. Commit changes with ``Back to dev [skip ci]`` message and push them to master.

.. _js-sandbox: https://github.com/pinepain/js-sandbox
