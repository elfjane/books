<?php
require_once '../lib/Functions.php';
loadEnv('../.env');
require_once('../config/config.php');
require_once '../vendor/autoload.php';

require_once '../bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as Capsule;

if (CRON_DEBUG_MODE) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    error_reporting(E_ALL);
    ini_set("memory_limit","-1");
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// 如果為空字串或 null，就指定預設值 "index"
if (empty($path) || $path === '/') {
    $path = 'index';
}

//echo $path;

$getclass = $path;

function __error_msg($error) {
    die($error . PHP_EOL);
}

function __pdo_error($instance, $error) {
	require_once(CRON_BASE_PATH ."/error_msg.php");
    $errorCode = 99;

    $data = array();
    $data['data']     = "";

    if (isset($lang_code[$errorCode])) {
        $errorCode = $lang_code[$errorCode];
	}
    $data['succ']     = $errorCode;

    if (isset($lang_layout[$errorCode])) {
        $data['error_layout'] = $lang_layout[$errorCode];
    }
    $data['errorMsg'] = $error->getMessage();

    $queries = Capsule::getQueryLog(); // 獲取 SQL 查詢日誌
    //var_dump($queries);
    if (isset($queries[0])) {
        error_log("Failed SQL: " . $queries[0]['query']);
        error_log("With Parameters: " . implode(', ', $queries[0]['bindings']));
    }
    $sql = '';
    $sqlParameters = '';
    if (!empty($queries)) {
        $sql = $queries[0]['query'];
        $sqlParameters = $queries[0]['bindings'];
    }

    $logdata = [
        'datetime' => date(CRON_BASE_DATE),
        'class' => $instance->classes,
        'sql' => $sql,
        'sql_parameters' => $sqlParameters,
        'error_code' => $error->getCode(),
        'error_msg' => $error->getMessage(),
        'data' => $data,
    ];
    $logFilename = CRON_BASE_PATH . '/logs/'.$instance->classes.'_pdo_debug_request_'.date(CRON_BASE_LOG_DATE).'.txt';
    //echo $logFilename;
    file_put_contents($logFilename, json_encode($logdata).PHP_EOL, FILE_APPEND);


    echo json_encode($data);
}

function __systemerror($error) {

	require_once(CRON_BASE_PATH ."/error_msg.php");
    $errorCode = $error->getCode();

    $data = array();
    $data['data']     = "";

    if (isset($lang_code[$errorCode])) {
        $errorCode = $lang_code[$errorCode];
	}
    $data['succ']     = $errorCode;

    if (isset($lang_layout[$errorCode])) {
        $data['error_layout'] = $lang_layout[$errorCode];
    }
    $data['errorMsg'] = $error->getMessage();

    echo json_encode($data);
}

if (CRON_DEBUG_MODE) {
    $debug_request = array();
    foreach($_REQUEST as $row => $val)
    {
        $debug_request[$row] = $val;
    }
    $logdata = "[".date(CRON_BASE_DATE)."] ".json_encode($debug_request) . "\t".json_encode(getallheaders()).PHP_EOL;
    //file_put_contents(CRON_BASE_PATH . '/logs/'.$getclass.'_debug_request_'.date(CRON_BASE_LOG_DATE).'.txt', $logdata, FILE_APPEND);
}

if (($strpos = strpos($getclass, '.')) != false) {
    $class = substr($getclass, 0, $strpos);
} else {
    $class = $getclass;
}

$parts = explode('/', trim($class, '/'));

// 每段首字母大寫
$parts = array_map('ucfirst', $parts);

$controller = array_pop($parts);

// 取最後一段 + Controller
$className = $controller . 'Controller';
// 拼完整 namespace
if (empty($parts)) {
    $classFullName = "App\\Http\\Controllers\\" . $className;
} else {
    $classFullName = "App\\Http\\Controllers\\" . implode('\\', $parts) . "\\" . $className;
}

//var_dump($classFullName);

if (!class_exists($classFullName)) {
    __error_msg('load_class');
}
use Lib\Tools\BenchmarkTimerTool;
use Lib\Tools\DisplayTool;
if (CRON_DEBUG_MODE_TIME) {
    $benchmark = new BenchmarkTimerTool();
    $benchmark->start();
}
set_error_handler(
    function ($errno, $errstr, $errfile, $errline) {

        switch ($errno) {
            case E_USER_ERROR:
                $errMsg = "Error type: E_USER_ERROR";
                break;
            case E_USER_WARNING:
                $errMsg = "Error type: E_USER_WARNING";
                break;
            case E_USER_NOTICE:
                $errMsg = "Error type: E_USER_NOTICE";
                break;
            case E_RECOVERABLE_ERROR:
                $errMsg = "Error type: E_RECOVERABLE_ERROR";
                break;
            case E_NOTICE:
                $errMsg = "Error type: E_NOTICE";
                break;
            default:
                $errMsg = "Error type: Unknown";
                break;
        }

        $errMsg .= "\n {$errstr} \n Fatal error on line $errline in file $errfile";
        ppError($errMsg);
        throw new Exception($errMsg, $errno);
    });

register_shutdown_function(function ()  {

    $errfile = 'unknown file';
    $errstr = 'shutdown';
    $errno = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== null) {
        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];

        ppError($error);
        // $err = new ErrorCodeParser();
        // $errOutput = $err->getUnexpectedError();
        //echo 123;

        // $app->response
        //     ->setStatusCode($errOutput['status_code'])
        //     ->sendHeaders();

        // if ($di->get('config')->get('DEBUG')) {
        //     $errOutput['error_result']['message'] .= "==  [$errno] $errfile:$errline -> $errstr";
        // }
        echo $errstr;
        //echo json_encode($errOutput['error_result']);
    }
});
try {
    // 使用 Reflection 自動產生依賴
    $reflector = new ReflectionClass($classFullName);
    $constructor = $reflector->getConstructor();
    $params = [];

    if ($constructor) {
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            $paramClass = $type instanceof ReflectionNamedType && !$type->isBuiltin() ? $type->getName() : null;

            if ($paramClass && class_exists($paramClass) && !(new ReflectionClass($paramClass))->isAbstract()) {
                // 自動 new class
                $params[] = new $paramClass();
            } elseif ($param->isDefaultValueAvailable()) {
                // 使用預設值
                $params[] = $param->getDefaultValue();
            } else {
                // 無法自動生成，使用 null（如果允許）
                $params[] = $type && $type->allowsNull() ? null : throw new Exception("Cannot resolve parameter '{$param->getName()}' for class '{$classFullName}'");
            }
        }
    }

    // 建立實例
    $instance = $reflector->newInstanceArgs($params);
    //$instance          = new $classFullName;
    //print_r(get_declared_classes());
    //$instance->class   = $class;
    $instance->classes = $className;
    $instance->__default();

    if (CRON_DEBUG_MODE_TIME) {
         $benchmark->stop();
         $runtime = $benchmark->timeElapsed();
         $instance->set('runtime', $runtime);
    }
    $display = new DisplayTool();

    $display->show($instance->display, $instance->displayData, $class, $instance);
} catch (PDOException $e) {
    __pdo_error($instance, $e);
} catch (Exception $error) {
    __systemerror($error);
}
