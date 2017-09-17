--TEST--
Check all extension entities
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

class Dumper
{
    public function dumpExtension()
    {
        $re = new ReflectionExtension('v8');

        echo 'Name: ', $re->getName(), PHP_EOL;
        echo 'Version: ', $re->getVersion(), PHP_EOL;

        echo PHP_EOL;
        echo 'Extension-global functions:', PHP_EOL;

        if ($re->getFunctions()) {
            foreach ($re->getFunctions() as $rf) {
                $this->dumpFunction($rf);
            }
        } else {
            echo 'none', PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Extension-global constants:', PHP_EOL;

        if ($re->getConstants()) {
            foreach ($re->getConstants() as $name => $value) {
                echo "$name = ", var_export($value, true), PHP_EOL;
            }
        } else {
            echo 'none', PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Extension-global classes:', PHP_EOL;

        if ($re->getClasses()) {
            foreach ($re->getClasses() as $rc) {
                $this->dumpClass($rc);
                echo PHP_EOL;
            }
        } else {
            echo 'none', PHP_EOL;
        }


    }

    protected function dumpClass(ReflectionClass $rc)
    {
        if ($rc->isTrait()) {
            echo 'trait ';
        } elseif ($rc->isInterface()) {
            echo 'interface ';
        } else {
            if ($rc->isAbstract()) {
                echo 'abstract ';
            }

            if ($rc->isFinal()) {
                echo 'final ';
            }

            echo 'class ';
        }

        echo $rc->getName(), PHP_EOL;

        if ($rc->getParentClass()) {
            echo '    extends ', $rc->getParentClass()->getName(), PHP_EOL;
        }

        foreach ($rc->getInterfaces() as $ri) {
            echo '    implements ', $ri->getName(), PHP_EOL;
        }

        foreach ($rc->getTraits() as $rt) {
            echo '    use ', $rt->getName(), PHP_EOL;
        }

        foreach ($rc->getConstants() as $name => $value) {
            echo "    const {$name} = ", var_export($value, true), PHP_EOL;
        }

        foreach ($rc->getProperties() as $rp) {
            if ($rp->getDeclaringClass() != $rc) {
                continue;
            }

            echo '    ';

            if ($rp->isStatic()) {
                echo 'static ';
            }


            if ($rp->isPublic()) {
                echo 'public ';
            }

            if ($rp->isProtected()) {
                echo 'protected ';
            }

            if ($rp->isPrivate()) {
                echo 'private ';
            }

            echo '$', $rp->getName();
            echo PHP_EOL;
        }



        foreach ($rc->getMethods() as $rm) {
            if ($rm->getDeclaringClass() != $rc) {
                continue;
            }

            echo '    ';

            if ($rm->isAbstract()) {
                echo 'abstract ';
            }

            if ($rm->isFinal()) {
                echo 'final ';
            }

            if ($rm->isPublic()) {
                echo 'public ';
            }

            if ($rm->isProtected()) {
                echo 'protected ';
            }

            if ($rm->isPrivate()) {
                echo 'private ';
            }

            if ($rm->isStatic()) {
                echo 'static ';
            }

            echo 'function ', $rm->getName();
            echo $this->dumpPartialFunction($rm), PHP_EOL;
        }
    }

    protected function dumpFunction(ReflectionFunction $rf)
    {
        if ($rf->inNamespace()) {
            echo $rf->getNamespaceName(), ': ';
        }

        echo 'function ', $rf->getName();

        echo $this->dumpPartialFunction($rf);
        echo PHP_EOL;
    }

    protected function dumpPartialFunction(ReflectionFunctionAbstract $rf)
    {
        $ret = '(';

        $parameters = [];
        foreach ($rf->getParameters() as $parameter) {
            $parameters[] = $this->dumpParameter($parameter);
        }

        $ret .= implode(', ', $parameters);

        $ret .= ')';

        if ($rf->hasReturnType()) {
            $ret .= ': ' . ($rf->getReturnType()->allowsNull() ? '?' : '') . $rf->getReturnType();
        }

        return $ret;
    }

    protected function dumpParameter(ReflectionParameter $rp)
    {
        $ret = [];

        $ret[] = $rp->hasType() ? ($rp->allowsNull() ? '?' : '') . (string)$rp->getType() : null;
        $ret[] = ($rp->isVariadic() ? '...' : '') . "\${$rp->getName()}";

        // $ret[] = $rp->isOptional() ? '= ?' : '';

        return trim(implode(' ', $ret));
    }
}

$d = new Dumper();

$d->dumpExtension();

// EXPECTF: ---/Version: .+/
// EXPECTF: +++Version: %s

?>
--EXPECTF--
Name: v8
Version: %s

Extension-global functions:
none

Extension-global constants:
none

Extension-global classes:
final class V8\AccessControl
    const DEFAULT_ACCESS = 0
    const ALL_CAN_READ = 1
    const ALL_CAN_WRITE = 2

final class V8\ConstructorBehavior
    const THROW = 0
    const ALLOW = 1

final class V8\IntegrityLevel
    const FROZEN = 0
    const SEALED = 1

final class V8\PropertyAttribute
    const NONE = 0
    const READ_ONLY = 1
    const DONT_ENUM = 2
    const DONT_DELETE = 4

final class V8\PropertyHandlerFlags
    const NONE = 0
    const ALL_CAN_READ = 1
    const NON_MASKING = 2
    const ONLY_INTERCEPT_STRINGS = 4

final class V8\PropertyFilter
    const ALL_PROPERTIES = 0
    const ONLY_WRITABLE = 1
    const ONLY_ENUMERABLE = 2
    const ONLY_CONFIGURABLE = 4
    const SKIP_STRINGS = 8
    const SKIP_SYMBOLS = 16

final class V8\KeyCollectionMode
    const OWN_ONLY = 0
    const INCLUDE_PROTOTYPES = 1

final class V8\IndexFilter
    const INCLUDE_INDICES = 0
    const SKIP_INDICES = 1

class V8\Exceptions\Exception
    extends Exception
    implements Throwable

class V8\Exceptions\TryCatchException
    extends V8\Exceptions\Exception
    implements Throwable
    private $isolate
    private $context
    private $try_catch
    public function __construct(V8\Isolate $isolate, V8\Context $context, V8\TryCatch $try_catch)
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function getTryCatch(): V8\TryCatch

class V8\Exceptions\TerminationException
    extends V8\Exceptions\TryCatchException
    implements Throwable

class V8\Exceptions\ResourceLimitException
    extends V8\Exceptions\TerminationException
    implements Throwable

class V8\Exceptions\TimeLimitException
    extends V8\Exceptions\ResourceLimitException
    implements Throwable

class V8\Exceptions\MemoryLimitException
    extends V8\Exceptions\ResourceLimitException
    implements Throwable

class V8\Exceptions\ValueException
    extends V8\Exceptions\Exception
    implements Throwable

interface V8\AdjustableExternalMemoryInterface
    abstract public function adjustExternalAllocatedMemory(int $change_in_bytes): int
    abstract public function getExternalAllocatedMemory(): int

class V8\HeapStatistics
    private $total_heap_size
    private $total_heap_size_executable
    private $total_physical_size
    private $total_available_size
    private $used_heap_size
    private $heap_size_limit
    private $malloced_memory
    private $peak_malloced_memory
    private $does_zap_garbage
    public function __construct(float $total_heap_size, float $total_heap_size_executable, float $total_physical_size, float $total_available_size, float $used_heap_size, float $heap_size_limit, float $malloced_memory, float $peak_malloced_memory, bool $does_zap_garbage)
    public function getTotalHeapSize(): float
    public function getTotalHeapSizeExecutable(): float
    public function getTotalPhysicalSize(): float
    public function getTotalAvailableSize(): float
    public function getUsedHeapSize(): float
    public function getHeapSizeLimit(): float
    public function getMallocedMemory(): float
    public function getPeakMallocedMemory(): float
    public function doesZapGarbage(): bool

class V8\StartupData
    public function __construct(string $blob)
    public function getData(): string
    public function getRawSize(): int
    public static function createFromSource(string $source): V8\StartupData
    public static function warmUpSnapshotDataBlob(V8\StartupData $cold_startup_data, string $warmup_source): V8\StartupData

class V8\Isolate
    const MEMORY_PRESSURE_LEVEL_NONE = 0
    const MEMORY_PRESSURE_LEVEL_MODERATE = 1
    const MEMORY_PRESSURE_LEVEL_CRITICAL = 2
    public function __construct(?V8\StartupData $snapshot)
    public function setTimeLimit(float $time_limit_in_seconds)
    public function getTimeLimit(): float
    public function isTimeLimitHit(): bool
    public function setMemoryLimit(int $memory_limit_in_bytes)
    public function getMemoryLimit(): int
    public function isMemoryLimitHit(): bool
    public function memoryPressureNotification(int $level)
    public function getHeapStatistics(): V8\HeapStatistics
    public function inContext(): bool
    public function getEnteredContext(): V8\Context
    public function throwException(V8\Context $context, V8\Value $value, Throwable $e)
    public function idleNotificationDeadline($deadline_in_seconds): bool
    public function lowMemoryNotification()
    public function terminateExecution()
    public function isExecutionTerminating(): bool
    public function cancelTerminateExecution()
    public function isDead(): bool
    public function isInUse(): bool
    public function setCaptureStackTraceForUncaughtExceptions(bool $capture, int $frame_limit)

class V8\Context
    private $isolate
    public function __construct(V8\Isolate $isolate, ?V8\ObjectTemplate $global_template, ?V8\ObjectValue $global_object)
    public function getIsolate(): V8\Isolate
    public function globalObject(): V8\ObjectValue
    public function detachGlobal()
    public function setSecurityToken(V8\Value $token)
    public function useDefaultSecurityToken()
    public function getSecurityToken(): V8\Value
    public function allowCodeGenerationFromStrings(bool $allow)
    public function isCodeGenerationFromStringsAllowed(): bool
    public function setErrorMessageForCodeGenerationFromStrings(V8\StringValue $message)

class V8\Script
    private $isolate
    private $context
    public function __construct(V8\Context $context, V8\StringValue $source, V8\ScriptOrigin $origin)
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function run(V8\Context $context): V8\Value
    public function getUnboundScript(): V8\UnboundScript

class V8\UnboundScript
    const kNoScriptId = 0
    private $isolate
    private function __construct()
    public function getIsolate(): V8\Isolate
    public function bindToContext(V8\Context $context): V8\Script
    public function getId(): int
    public function getScriptName(): V8\Value
    public function getSourceURL(): V8\Value
    public function getSourceMappingURL(): V8\Value
    public function getLineNumber(int $code_pos): int

class V8\ScriptCompiler\CachedData
    public function __construct(string $data)
    public function getData(): string
    public function isRejected(): bool

class V8\ScriptCompiler\Source
    private $source_string
    private $origin
    private $cached_data
    public function __construct(V8\StringValue $source_string, ?V8\ScriptOrigin $origin, ?V8\ScriptCompiler\CachedData $cached_data)
    public function getSourceString(): V8\StringValue
    public function getScriptOrigin(): ?V8\ScriptOrigin
    public function getCachedData(): ?V8\ScriptCompiler\CachedData

class V8\ScriptCompiler
    const OPTION_NO_COMPILE_OPTIONS = 0
    const OPTION_PRODUCE_PARSER_CACHE = 1
    const OPTION_CONSUME_PARSER_CACHE = 2
    const OPTION_PRODUCE_CODE_CACHE = 3
    const OPTION_CONSUME_CODE_CACHE = 4
    public static function cachedDataVersionTag(): int
    public static function compileUnboundScript(V8\Context $context, V8\ScriptCompiler\Source $source, int $options): V8\UnboundScript
    public static function compile(V8\Context $context, V8\ScriptCompiler\Source $source, int $options): V8\Script
    public static function compileFunctionInContext(V8\Context $context, V8\ScriptCompiler\Source $source, array $arguments, array $context_extensions): V8\FunctionObject

class V8\ExceptionManager
    public static function createRangeError(V8\Context $context, V8\StringValue $message): V8\ObjectValue
    public static function createReferenceError(V8\Context $context, V8\StringValue $message): V8\ObjectValue
    public static function createSyntaxError(V8\Context $context, V8\StringValue $message): V8\ObjectValue
    public static function createTypeError(V8\Context $context, V8\StringValue $message): V8\ObjectValue
    public static function createError(V8\Context $context, V8\StringValue $message): V8\ObjectValue
    public static function createMessage(V8\Context $context, V8\Value $exception): V8\Message
    public static function getStackTrace(V8\Context $context, V8\Value $exception): ?V8\StackTrace

class V8\TryCatch
    private $isolate
    private $context
    private $exception
    private $stack_trace
    private $message
    private $can_continue
    private $has_terminated
    private $external_exception
    public function __construct(V8\Isolate $isolate, V8\Context $context, ?V8\Value $exception, ?V8\Value $stack_trace, ?V8\Message $message, bool $can_continue, bool $has_terminated, ?Throwable $external_exception)
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function exception(): ?V8\Value
    public function stackTrace(): ?V8\Value
    public function message(): ?V8\Message
    public function canContinue(): bool
    public function hasTerminated(): bool
    public function getExternalException(): ?Throwable

class V8\Message
    const ERROR_LEVEL_LOG = 1
    const ERROR_LEVEL_DEBUG = 2
    const ERROR_LEVEL_INFO = 4
    const ERROR_LEVEL_ERROR = 8
    const ERROR_LEVEL_WARNING = 16
    const ERROR_LEVEL_ALL = 31
    private $message
    private $script_origin
    private $source_line
    private $resource_name
    private $stack_trace
    private $line_number
    private $start_position
    private $end_position
    private $start_column
    private $end_column
    private $error_level
    public function __construct(string $message, string $source_line, V8\ScriptOrigin $script_origin, string $resource_name, V8\StackTrace $stack_trace, ?int $line_number, ?int $start_position, ?int $end_position, ?int $start_column, ?int $end_column, ?int $error_level)
    public function get(): string
    public function getSourceLine(): string
    public function getScriptOrigin(): V8\ScriptOrigin
    public function getScriptResourceName(): string
    public function getStackTrace(): ?V8\StackTrace
    public function getLineNumber(): ?int
    public function getStartPosition(): ?int
    public function getEndPosition(): ?int
    public function getStartColumn(): ?int
    public function getEndColumn(): ?int
    public function getErrorLevel(): ?int

class V8\StackFrame
    private $line_number
    private $column
    private $script_id
    private $script_name
    private $script_name_or_source_url
    private $function_name
    private $is_eval
    private $is_constructor
    private $is_wasm
    public function __construct(?int $line_number, ?int $column, ?int $script_id, string $script_name, string $script_name_or_source_url, string $function_name, bool $is_eval, bool $is_constructor, bool $is_wasm)
    public function getLineNumber(): ?int
    public function getColumn(): ?int
    public function getScriptId(): ?int
    public function getScriptName(): string
    public function getScriptNameOrSourceURL(): string
    public function getFunctionName(): string
    public function isEval(): bool
    public function isConstructor(): bool
    public function isWasm(): bool

class V8\StackTrace
    const MIN_FRAME_LIMIT = 0
    const MAX_FRAME_LIMIT = 1000
    private $frames
    public function __construct(array $frames)
    public function getFrames(): array
    public function getFrame(int $index): V8\StackFrame
    public function getFrameCount(): int
    public static function currentStackTrace(V8\Isolate $isolate, int $frame_limit): V8\StackTrace

class V8\ScriptOriginOptions
    const IS_SHARED_CROSS_ORIGIN = 1
    const IS_OPAQUE = 2
    const IS_WASM = 4
    const IS_MODULE = 8
    private $flags
    public function __construct(int $options)
    public function getFlags(): int
    public function isSharedCrossOrigin(): bool
    public function isOpaque(): bool
    public function isWasm(): bool
    public function isModule(): bool

class V8\ScriptOrigin
    private $resource_name
    private $resource_line_offset
    private $resource_column_offset
    private $script_id
    private $source_map_url
    private $options
    public function __construct(string $resource_name, ?int $resource_line_offset, ?int $resource_column_offset, ?int $script_id, string $source_map_url, ?V8\ScriptOriginOptions $options)
    public function resourceName(): string
    public function resourceLineOffset(): ?int
    public function resourceColumnOffset(): ?int
    public function scriptId(): ?int
    public function sourceMapUrl(): string
    public function options(): V8\ScriptOriginOptions

class V8\Data

abstract class V8\Value
    extends V8\Data
    private $isolate
    public function getIsolate(): V8\Isolate
    public function isUndefined(): bool
    public function isNull(): bool
    public function isNullOrUndefined(): bool
    public function isTrue(): bool
    public function isFalse(): bool
    public function isName(): bool
    public function isString(): bool
    public function isSymbol(): bool
    public function isFunction(): bool
    public function isArray(): bool
    public function isObject(): bool
    public function isBoolean(): bool
    public function isNumber(): bool
    public function isInt32(): bool
    public function isUint32(): bool
    public function isDate(): bool
    public function isArgumentsObject(): bool
    public function isBooleanObject(): bool
    public function isNumberObject(): bool
    public function isStringObject(): bool
    public function isSymbolObject(): bool
    public function isNativeError(): bool
    public function isRegExp(): bool
    public function isAsyncFunction(): bool
    public function isGeneratorFunction(): bool
    public function isGeneratorObject(): bool
    public function isPromise(): bool
    public function isMap(): bool
    public function isSet(): bool
    public function isMapIterator(): bool
    public function isSetIterator(): bool
    public function isWeakMap(): bool
    public function isWeakSet(): bool
    public function isArrayBuffer(): bool
    public function isArrayBufferView(): bool
    public function isTypedArray(): bool
    public function isUint8Array(): bool
    public function isUint8ClampedArray(): bool
    public function isInt8Array(): bool
    public function isUint16Array(): bool
    public function isInt16Array(): bool
    public function isUint32Array(): bool
    public function isInt32Array(): bool
    public function isFloat32Array(): bool
    public function isFloat64Array(): bool
    public function isDataView(): bool
    public function isSharedArrayBuffer(): bool
    public function isProxy(): bool
    public function toBoolean(V8\Context $context): V8\BooleanValue
    public function toNumber(V8\Context $context): V8\NumberValue
    public function toString(V8\Context $context): V8\StringValue
    public function toDetailString(V8\Context $context): V8\StringValue
    public function toObject(V8\Context $context): V8\ObjectValue
    public function toInteger(V8\Context $context): V8\IntegerValue
    public function toUint32(V8\Context $context): V8\Uint32Value
    public function toInt32(V8\Context $context): V8\Int32Value
    public function toArrayIndex(V8\Context $context): V8\Uint32Value
    public function booleanValue(V8\Context $context): bool
    public function numberValue(V8\Context $context): float
    public function integerValue(V8\Context $context): float
    public function int32Value(V8\Context $context): int
    public function uint32Value(V8\Context $context): int
    public function equals(V8\Context $context, V8\Value $that): ?bool
    public function strictEquals(V8\Value $that): bool
    public function sameValue(V8\Value $that): bool
    public function typeOf(): V8\StringValue
    public function instanceOf(V8\Context $context, V8\ObjectValue $object): bool

abstract class V8\PrimitiveValue
    extends V8\Value

class V8\UndefinedValue
    extends V8\PrimitiveValue
    public function __construct(V8\Isolate $isolate)
    public function value()

class V8\NullValue
    extends V8\PrimitiveValue
    public function __construct(V8\Isolate $isolate)
    public function value()

class V8\BooleanValue
    extends V8\PrimitiveValue
    public function __construct(V8\Isolate $isolate, bool $value)
    public function value(): bool

abstract class V8\NameValue
    extends V8\PrimitiveValue
    public function getIdentityHash(): int

class V8\StringValue
    extends V8\NameValue
    const MAX_LENGTH = 1073741799
    public function __construct(V8\Isolate $isolate, $data)
    public function value(): string
    public function length(): int
    public function utf8Length(): int
    public function isOneByte(): bool
    public function containsOnlyOneByte(): bool

class V8\SymbolValue
    extends V8\NameValue
    public function __construct(V8\Isolate $isolate, ?V8\StringValue $name)
    public function value(): string
    public function name(): V8\Value
    public static function for(V8\Context $context, V8\StringValue $name): V8\SymbolValue
    public static function forApi(V8\Context $context, V8\StringValue $name): V8\SymbolValue
    public static function getHasInstance(V8\Isolate $isolate): V8\SymbolValue
    public static function getIsConcatSpreadable(V8\Isolate $isolate): V8\SymbolValue
    public static function getIterator(V8\Isolate $isolate): V8\SymbolValue
    public static function getMatch(V8\Isolate $isolate): V8\SymbolValue
    public static function getReplace(V8\Isolate $isolate): V8\SymbolValue
    public static function getSearch(V8\Isolate $isolate): V8\SymbolValue
    public static function getSplit(V8\Isolate $isolate): V8\SymbolValue
    public static function getToPrimitive(V8\Isolate $isolate): V8\SymbolValue
    public static function getToStringTag(V8\Isolate $isolate): V8\SymbolValue
    public static function getUnscopables(V8\Isolate $isolate): V8\SymbolValue

class V8\NumberValue
    extends V8\PrimitiveValue
    public function __construct(V8\Isolate $isolate, float $value)
    public function value()

class V8\IntegerValue
    extends V8\NumberValue
    public function __construct(V8\Isolate $isolate, int $value)
    public function value(): int

class V8\Int32Value
    extends V8\IntegerValue
    public function __construct(V8\Isolate $isolate, int $value)
    public function value(): int

class V8\Uint32Value
    extends V8\IntegerValue
    public function __construct(V8\Isolate $isolate, int $value)
    public function value(): int

class V8\ObjectValue
    extends V8\Value
    implements V8\AdjustableExternalMemoryInterface
    private $context
    public function __construct(V8\Context $context)
    public function getContext(): V8\Context
    public function set(V8\Context $context, V8\Value $key, V8\Value $value)
    public function createDataProperty(V8\Context $context, V8\NameValue $key, V8\Value $value): bool
    public function defineOwnProperty(V8\Context $context, V8\NameValue $key, V8\Value $value, $attributes): bool
    public function get(V8\Context $context, V8\Value $key): V8\Value
    public function getPropertyAttributes(V8\Context $context, V8\StringValue $key): int
    public function getOwnPropertyDescriptor(V8\Context $context, V8\StringValue $key): V8\Value
    public function has(V8\Context $context, V8\Value $key): bool
    public function delete(V8\Context $context, V8\Value $key): bool
    public function setAccessor(V8\Context $context, V8\NameValue $name, callable $getter, ?callable $setter, int $settings, int $attributes): bool
    public function setAccessorProperty(V8\NameValue $name, V8\FunctionObject $getter, V8\FunctionObject $setter, int $attributes, int $settings)
    public function setNativeDataProperty(V8\Context $context, V8\NameValue $name, callable $getter, ?callable $setter, int $attributes): bool
    public function getPropertyNames(V8\Context $context, int $mode, int $property_filter, int $index_filter): V8\ArrayObject
    public function getOwnPropertyNames(V8\Context $context, int $filter): V8\ArrayObject
    public function getPrototype(): V8\Value
    public function setPrototype(V8\Context $context, V8\Value $prototype): bool
    public function findInstanceInPrototypeChain(V8\FunctionTemplate $tmpl): V8\ObjectValue
    public function objectProtoToString(V8\Context $context): V8\StringValue
    public function getConstructorName(): V8\StringValue
    public function setIntegrityLevel(V8\Context $context, int $level): bool
    public function hasOwnProperty(V8\Context $context, V8\NameValue $key): bool
    public function hasRealNamedProperty(V8\Context $context, V8\NameValue $key): bool
    public function hasRealIndexedProperty(V8\Context $context, int $index): bool
    public function hasRealNamedCallbackProperty(V8\Context $context, V8\NameValue $key): bool
    public function getRealNamedPropertyInPrototypeChain(V8\Context $context, V8\NameValue $key): V8\Value
    public function getRealNamedPropertyAttributesInPrototypeChain(V8\Context $context, V8\NameValue $key): int
    public function getRealNamedProperty(V8\Context $context, V8\NameValue $key): V8\Value
    public function getRealNamedPropertyAttributes(V8\Context $context, V8\NameValue $key): int
    public function hasNamedLookupInterceptor(): bool
    public function hasIndexedLookupInterceptor(): bool
    public function getIdentityHash(): int
    public function clone(): V8\ObjectValue
    public function isCallable(): bool
    public function isConstructor(): bool
    public function callAsFunction(V8\Context $context, V8\Value $recv, array $arguments): V8\Value
    public function callAsConstructor(V8\Context $context, array $arguments): V8\Value
    public function adjustExternalAllocatedMemory(int $change_in_bytes): int
    public function getExternalAllocatedMemory(): int

class V8\FunctionObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, callable $callback, int $length)
    public function newInstance(V8\Context $context, array $arguments): V8\ObjectValue
    public function call(V8\Context $context, V8\Value $recv, array $arguments): V8\Value
    public function setName(V8\StringValue $name)
    public function getName(): V8\Value
    public function getInferredName(): V8\Value
    public function getDisplayName(): V8\Value
    public function getScriptLineNumber(): ?int
    public function getScriptColumnNumber(): ?int
    public function getScriptId(): ?int
    public function getBoundFunction(): V8\Value
    public function getScriptOrigin(): V8\ScriptOrigin

class V8\ArrayObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, int $length)
    public function length(): int

class V8\MapObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context)
    public function size(): float
    public function clear()
    public function get(V8\Context $context, V8\Value $key): V8\Value
    public function set(V8\Context $context, V8\Value $key, V8\Value $value): V8\MapObject
    public function has(V8\Context $context, V8\Value $key): bool
    public function delete(V8\Context $context, V8\Value $key): bool
    public function asArray(): V8\ArrayObject

class V8\SetObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context)
    public function size(): float
    public function clear()
    public function add(V8\Context $context, V8\Value $key): V8\SetObject
    public function has(V8\Context $context, V8\Value $key): bool
    public function delete(V8\Context $context, V8\Value $key): bool
    public function asArray(): V8\ArrayObject

class V8\DateObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, float $time)
    public function valueOf(): float
    public static function dateTimeConfigurationChangeNotification(V8\isolate $isolate)

class V8\RegExpObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    const FLAG_NONE = 0
    const FLAG_GLOBAL = 1
    const FLAG_IGNORE_CASE = 2
    const FLAG_MULTILINE = 4
    const FLAG_STICKY = 8
    const FLAG_UNICODE = 16
    const FLAG_DOTALL = 32
    public function __construct(V8\Context $context, V8\StringValue $context, ?int $flags)
    public function getSource(): V8\StringValue
    public function getFlags(): int

class V8\PromiseObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    const STATE_PENDING = 0
    const STATE_FULFILLED = 1
    const STATE_REJECTED = 2
    public function __construct(V8\Context $context)
    public function resolve(V8\Context $context, V8\Value $value)
    public function reject(V8\Context $context, V8\Value $value)
    public function catch(V8\Context $context, V8\FunctionObject $handler): V8\PromiseObject
    public function then(V8\Context $context, V8\FunctionObject $handler): V8\PromiseObject
    public function hasHandler(): bool
    public function result(): V8\Value
    public function state(): int

class V8\ProxyObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, V8\ObjectValue $target, V8\ObjectValue $handler)
    public function getTarget(): V8\ObjectValue
    public function getHandler(): V8\Value
    public function isRevoked(): bool
    public function revoke()

class V8\NumberObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, $value)
    public function valueOf(): float

class V8\BooleanObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, bool $value)
    public function valueOf(): bool

class V8\StringObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, V8\StringValue $value)
    public function valueOf(): V8\StringValue

class V8\SymbolObject
    extends V8\ObjectValue
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Context $context, V8\SymbolValue $value)
    public function valueOf(): V8\SymbolValue

abstract class V8\Template
    extends V8\Data
    private $isolate
    abstract public function getIsolate(): V8\Isolate
    abstract public function set(V8\NameValue $name, V8\Data $value, int $attributes)
    abstract public function setAccessorProperty(V8\NameValue $name, V8\FunctionTemplate $getter, V8\FunctionTemplate $setter, int $attributes, int $settings)
    abstract public function setNativeDataProperty(V8\NameValue $name, callable $getter, ?callable $setter, int $attributes, ?V8\FunctionTemplate $receiver, int $settings)

class V8\ObjectTemplate
    extends V8\Template
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Isolate $isolate, ?V8\FunctionTemplate $constructor)
    public function getIsolate(): V8\Isolate
    public function set(V8\NameValue $name, V8\Data $value, int $attributes)
    public function setAccessorProperty(V8\NameValue $name, V8\FunctionTemplate $getter, V8\FunctionTemplate $setter, int $attributes, int $settings)
    public function setNativeDataProperty(V8\NameValue $name, callable $getter, ?callable $setter, int $attributes, ?V8\FunctionTemplate $receiver, int $settings)
    public function newInstance(V8\Context $value): V8\ObjectValue
    public function setAccessor(V8\NameValue $name, callable $getter, ?callable $setter, int $settings, int $attributes, ?V8\FunctionTemplate $receiver)
    public function setHandlerForNamedProperty(V8\NamedPropertyHandlerConfiguration $configuration)
    public function setHandlerForIndexedProperty(V8\IndexedPropertyHandlerConfiguration $configuration)
    public function setCallAsFunctionHandler($callback)
    public function isImmutableProto(): bool
    public function setImmutableProto()
    public function adjustExternalAllocatedMemory(int $change_in_bytes): int
    public function getExternalAllocatedMemory(): int

class V8\FunctionTemplate
    extends V8\Template
    implements V8\AdjustableExternalMemoryInterface
    public function __construct(V8\Isolate $isolate, ?callable $callback, ?V8\FunctionTemplate $receiver, int $length, int $behavior)
    public function getIsolate(): V8\Isolate
    public function set(V8\NameValue $name, V8\Data $value, int $attributes)
    public function setAccessorProperty(V8\NameValue $name, V8\FunctionTemplate $getter, V8\FunctionTemplate $setter, int $attributes, int $settings)
    public function setNativeDataProperty(V8\NameValue $name, callable $getter, ?callable $setter, int $attributes, ?V8\FunctionTemplate $receiver, int $settings)
    public function getFunction(V8\Context $context): V8\FunctionObject
    public function setCallHandler(callable $callback)
    public function setLength(int $length)
    public function instanceTemplate(): V8\ObjectTemplate
    public function inherit(V8\FunctionTemplate $parent)
    public function prototypeTemplate(): V8\ObjectTemplate
    public function setClassName(V8\StringValue $name)
    public function setAcceptAnyReceiver(bool $value)
    public function setHiddenPrototype(bool $value)
    public function readOnlyPrototype()
    public function removePrototype()
    public function hasInstance(V8\ObjectValue $object): bool
    public function adjustExternalAllocatedMemory(int $change_in_bytes): int
    public function getExternalAllocatedMemory(): int

class V8\ReturnValue
    private $isolate
    private $context
    public function get(): V8\Value
    public function set(V8\Value $value)
    public function setNull()
    public function setUndefined()
    public function setEmptyString()
    public function setBool(bool $value)
    public function setInteger(int $i)
    public function setFloat(float $i)
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function inContext(): bool

class V8\PropertyCallbackInfo
    private $isolate
    private $context
    private $this
    private $holder
    private $return_value
    private $should_throw_on_error
    public function this(): V8\ObjectValue
    public function holder(): V8\ObjectValue
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function getReturnValue(): V8\ReturnValue
    public function shouldThrowOnError(): bool

class V8\FunctionCallbackInfo
    private $isolate
    private $context
    private $this
    private $holder
    private $return_value
    private $arguments
    private $new_target
    private $is_constructor_call
    public function this(): V8\ObjectValue
    public function holder(): V8\ObjectValue
    public function getIsolate(): V8\Isolate
    public function getContext(): V8\Context
    public function getReturnValue(): V8\ReturnValue
    public function length(): int
    public function arguments(): array
    public function newTarget(): V8\Value
    public function isConstructCall(): bool

class V8\NamedPropertyHandlerConfiguration
    public function __construct(callable $getter, ?callable $setter, ?callable $query, ?callable $deleter, ?callable $enumerator, int $flags)

class V8\IndexedPropertyHandlerConfiguration
    public function __construct(callable $getter, ?callable $setter, ?callable $query, ?callable $deleter, ?callable $enumerator, int $flags)

class V8\JSON
    public static function parse(V8\Context $context, V8\StringValue $json_string): V8\Value
    public static function stringify(V8\Context $context, V8\Value $json_value, ?V8\StringValue $gap): string
