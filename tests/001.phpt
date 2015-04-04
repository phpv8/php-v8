--TEST--
Check for v8 presence
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php 
echo "v8 extension is available";
?>
--EXPECT--
v8 extension is available
