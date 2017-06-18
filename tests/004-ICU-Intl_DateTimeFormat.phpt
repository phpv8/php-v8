--TEST--
ICU - Intl.DateTimeFormat()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
TZ=UTC
--INI--
date.timezone = "UTC"
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

// @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat#Using_locales
$source = /** @lang JavaScript 1.8 */
    <<<HEREDOC
var date = new Date(Date.UTC(2012, 11, 20, 3, 0, 0));

// formats below assume the local time zone of the locale;
// America/Los_Angeles for the US

// US English uses month-day-year order
console.log(new Intl.DateTimeFormat('en-US').format(date));
// → "12/19/2012"

// British English uses day-month-year order
console.log(new Intl.DateTimeFormat('en-GB').format(date));
// → "20/12/2012"

// Korean uses year-month-day order
console.log(new Intl.DateTimeFormat('ko-KR').format(date));
// → "2012. 12. 20."

// Arabic in most Arabic speaking countries uses real Arabic digits
console.log(new Intl.DateTimeFormat('ar-EG').format(date));
// → "٢٠‏/١٢‏/٢٠١٢"

// for Japanese, applications may want to use the Japanese calendar,
// where 2012 was the year 24 of the Heisei era
console.log(new Intl.DateTimeFormat('ja-JP-u-ca-japanese').format(date));
// → "24/12/20"

// when requesting a language that may not be supported, such as
// Balinese, include a fallback language, in this case Indonesian
console.log(new Intl.DateTimeFormat(['ban', 'id']).format(date));
// → "20/12/2012"
HEREDOC;

(new \V8\Script($context, new \V8\StringValue($isolate, $source)))->Run($context);

$helper->line();


// @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat#Using_options
// with en-AU am/AM hack as it causes troubles in CI and inconsistent across different ICU versions
$source = /** @lang JavaScript 1.8 */
    <<<HEREDOC
var date = new Date(Date.UTC(2012, 11, 20, 3, 0, 0));

// request a weekday along with a long date
var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
console.log(new Intl.DateTimeFormat('de-DE', options).format(date));
// → "Donnerstag, 20. Dezember 2012"

// an application may want to use UTC and make that visible
options.timeZone = 'UTC';
options.timeZoneName = 'short';
console.log(new Intl.DateTimeFormat('en-US', options).format(date));
// → "Thursday, December 20, 2012, UTC"

// sometimes you want to be more precise
var options = {
  hour: 'numeric', minute: 'numeric', second: 'numeric',
  timeZoneName: 'short'
};
console.log(new Intl.DateTimeFormat('en-AU', options).format(date).replace('AM', 'am'));
// → "2:00:00 pm AEDT"

// sometimes even the US needs 24-hour time
options = {
  year: 'numeric', month: 'numeric', day: 'numeric',
  hour: 'numeric', minute: 'numeric', second: 'numeric',
  hour12: false
};
console.log(date.toLocaleString('en-US', options));
// → "12/19/2012, 19:00:00"
HEREDOC;

(new \V8\Script($context, new \V8\StringValue($isolate, $source)))->Run($context);


$helper->line();

$source = /** @lang JavaScript 1.8 */
    <<<HEREDOC
var date = new Date(Date.UTC(2012, 11, 20, 3, 0, 0));

// request a weekday along with a long date
var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

options.timeZone = 'America/New_York';
console.log(new Intl.DateTimeFormat('en-US', options).format(date));
options.timeZone = 'Europe/Paris';
console.log(new Intl.DateTimeFormat('fr-FR', options).format(date));
options.timeZone = 'Europe/Berlin';
console.log(new Intl.DateTimeFormat('de-DE', options).format(date));

HEREDOC;

(new \V8\Script($context, new \V8\StringValue($isolate, $source)))->Run($context);


?>
--EXPECT--
12/20/2012
20/12/2012
2012. 12. 20.
٢٠‏/١٢‏/٢٠١٢
平成24/12/20
20/12/2012

Donnerstag, 20. Dezember 2012
Thursday, December 20, 2012, UTC
3:00:00 am UTC
12/20/2012, 03:00:00

Wednesday, December 19, 2012
jeudi 20 décembre 2012
Donnerstag, 20. Dezember 2012
