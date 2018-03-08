*************
Release libv8
*************

We will start with building Ubuntu PPA first as it used in further CI process to validate that ``php-v8`` is not broken.

To track v8 changes you can use these links:

* https://github.com/v8/v8/commits/master/include/v8.h - to keep track on v8 upstream changes
* https://omahaproxy.appspot.com/ - to keep track v8 channel(version) heads and what version is used in chrome

Building libv8
==============

#. **Skip this step if you are updating v8 patch release version.** To bump minor v8 version (e.g. from 6.3 to 6.4),
   create new ``libv8-X.Y`` PPA for new version. As V8 could be build for ``i386`` but only from ``amd64``, which is not how PPA
   works, it's also make sense to keep new PPA ``amd64``-only. Also we don't use ``i386`` so we don't want to worry about it.
#. Update libv8 Makefile (``packaging/libv8/Makefile``) with new libv8 version by setting proper values in
   ``GIT_VERSION=X.Y.Z`` and ``NAME=libv8-X.Y`` variables.
#. Commit changes with ``build libv8`` commit message and wait until libv8 PPA build done.

After libv8 PPA build done
==========================

#. Copy fresh ``libv8-X.Y`` build packages from ``experimental`` (default target for all libv8 builds we trigger)
   to it ``libv8-X.Y`` PPA. Do not rebuild, just copy binaries.
#. **Wait for packages copied and published!**
#. Build `pinepain/libv8`_ docker image, tag it with the
   relevant v8 full version and push to Docker Hub.
#. You may want to set proper ``V8`` version in ``php-v8`` by updating it in ``.travis.yml``.
#. Make sure you have proper ``V8`` version set in ``packaging/Dockerfile`` under ``V8`` constant.

After docker images rebuilt/published
=====================================

#. Update min required ``libv8`` version in `php-v8`_ ``config.m4``, ``V8_MIN_API_VERSION_STR=X.Y.Z``.
#. If there was new docker images published, update reference to them in `php-v8`_ ``.travis.yml``
   and in `php-v8`_ ``Dockerfile``, and set proper ``V8`` and ``TAG`` value there.
#. Update reference to ``v8@X.Y`` in `php-v8`_ `CMakeLists.txt` on minor version bump.
#. Also, update references to v8 version in `php-v8`_/scripts/provision/provision.sh,
   it's normally could be done by replacing old version with new, e.g. ``6.3`` => ``6.4``.
#. On every version bump update `php-v8`_ ``README.md`` file with proper min v8 version required/tested.
#. If you use vagrant, re-provision your local development environment at this step to fetch/add new ``libv8`` version.
   It's generally a good idea to remove old ``libv8`` versions as well and remove their PPA from apt sources list at this point.
#. **Make sure** you tested `php-v8`_ locally first before pushing to remote,
   upgrading v8 could be tricky as it may break BC even in patch releases (that's why we started to have separate
   PPAs for minor version to somehow couple with this issue in minor releases).
#. Note, that doing all this in a separate branch and merging that later into master is a nice and safe idea
   (note, you may skip PR overhead and do fast-forward merge locally to master).
#. Commit message should state that it is v8 version bump, e.g. ``Require libv8 >= X.Y.Z``
#. Push changes and make sure build is green. If not, fix code/update tests and repeat.


Building packages for macOS Homebrew
====================================

#. **Skip this step if you are updating v8 patch release version.** If it is a minor version bump, create new ``v8@X.Y`` formula.
#. **Skip this step if you are updating v8 patch release version.** Create new ``v8:X.Y`` Package on bintray for it.
#. Remove/reset formula ``revision`` if it is version bump and not rebuild.
#. Build ``v8@X.Y`` (locally or with TravisCI if it provides relevant macOS version) and publish.

.. _php-v8: https://github.com/pinepain/php-v8
.. _pinepain/libv8: https://github.com/pinepain/dockerfiles/tree/master/libv8
