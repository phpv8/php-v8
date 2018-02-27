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

Ubuntu PPA release
==================

#. Copy targeted ``libv8-X.Y`` build to ``php`` ppa without rebuild, just copy.
#. Make sure you have proper PHP and ``php-v8`` PPA dependencies set in https://launchpad.net/~pinepain/+archive/ubuntu/php-v8/+edit-dependencies
#. Make sure you have proper ``php-v8`` version set in ``packaging/Dockerfile`` under ``V8`` constant.
#. In ``packaging/php-v8/Makefile`` set proper ``VERSION=X.Y.Z``
#. Make sure you have valid ``libv8`` dependency in ``packaging/php-v8/debian/control`` file.
#. Commit changes with ``build php-v8`` commit message and wait until libv8 PPA build done.
#. Copy ``php-v8`` packages to ``pinepain/php`` PPA, do not rebuild, just copy.
#. After they get copied, feels free to remove **old** ``libv8`` packages from ``pinepain/php`` ppa.

macOS Homebrew release
======================

#. Update ``php7*-v8`` formula **one by one** to have proper ``depends_on 'v8@X.Y'``
   and ``v8_prefix=Formula['v8@X.Y'].opt_prefix`` values.
#. If you want to rebuild existent version, add/increment ``revision`` in formula body.
#. If version has already been published to bintray and you absolutely sure it needs to be re-built without revision.
   bump, you will need to delete such version from bintray first.

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

#. Update `js-sandbox`_ ``.travis.yml`` and ``.scrutinizer.yml``
   to refer to new ``php-v8`` version and to relevant ``libv8`` PPA and packages.
#. Update ``PHP_V8_VERSION`` to the next version and set ``PHP_V8_REVISION`` to ``dev`` in ``php_v8.h``.
#. Commit changes with ``Back to dev [skip ci]`` message and push them to master.

.. _js-sandbox: https://github.com/pinepain/js-sandbox
