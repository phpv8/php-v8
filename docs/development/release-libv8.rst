*************
Release libv8
*************

Currently Docker is the recommended way to distribute and use both php-v8 and libv8 itself. We also support building
``libv8`` in macOS with Homebrew via `phpv8/tap`_ tap.

To track v8 changes you can use these links:

* https://github.com/v8/v8/commits/master/include/v8.h - to keep track on v8 upstream changes
* https://omahaproxy.appspot.com/ - to keep track v8 channel(version) heads and what version is used in chrome

Building docker image
=====================

#. Build `phpv8/libv8`_ docker image, tag it with the relevant v8 full version and push to Docker Hub.
   Hint: use ``Makefile``.
#. You may want to set proper ``V8`` version in ``php-v8`` by updating it in ``.travis.yml``.

After docker images rebuilt/published
=====================================

#. Update min required ``libv8`` version in `php-v8`_ ``config.m4``, ``V8_MIN_API_VERSION_STR=X.Y.Z``.
#. If there was new docker images published, update reference to them in `php-v8`_ ``.travis.yml``
   and in `php-v8`_ ``Dockerfile``, and set proper ``V8`` and ``TAG`` value there.
#. Update reference to ``v8@X.Y`` in `php-v8`_ `CMakeLists.txt` on minor version bump.
#. Also, update references to v8 version in `php-v8`_/scripts/provision/provision.sh,
   it's normally could be done by replacing old version with new, e.g. ``6.3`` => ``6.4``.
#. On every version bump update `php-v8`_ ``README.md`` file with proper min v8 version required/tested.
#. **Make sure** you tested `php-v8`_ locally first before pushing to remote,
   upgrading v8 could be tricky as it may break BC even in patch releases.
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

.. _php-v8: https://github.com/phpv8/php-v8
.. _phpv8/libv8: https://github.com/phpv8/dockerfiles/tree/master/libv8
.. _phpv8/tap: https://github.com/phpv8/homebrew-tap
