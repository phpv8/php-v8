--TEST--
Check for v8 presence
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
echo "v8 extension is available";
?>
--EXPECT--
v8 extension is available
