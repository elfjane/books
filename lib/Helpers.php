<?php
use Lib\Tools\ConfigTool;

use Lib\Logger\Formatter\CustomJsonFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


if (!function_exists('config')) {
    function config(string $key, $default = null) {
        return ConfigTool::get($key, $default);
    }
}

function getLogUuid()
{
    global $logUuid;

    if (empty($logUuid)) {
        $logUuid = md5(uniqid(rand()));
    }

    return $logUuid;
}

function getSession($key = null)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start(); // 確保 session 已啟動
    }

    return session_id();
}

function setSession($key, $value)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION[$key] = $value;
}
function getLogName($filename)
{
    $date = config('app.logDate',date("Ymd"));
    $logDir = __DIR__ . "/../logs/" . $date;
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    return $logDir . '/' . $filename . '_' . $date . ".log";
}

function convertMsg($message)
{
    if ($message instanceof Exception) {
        $e = $message;
        $exceptionMsg =
            "Error code(" . $message->getCode() . ") \n"
            . get_class($e)
            . " trace: \n" . $e->getMessage() . "\n"
            . " File = " . $e->getFile() . " on line(" . $e->getLine() . ")\n"
            . $e->getTraceAsString() . "\n";

        return $exceptionMsg;
    } else {
        $message = print_r($message, true);
    }

    return $message;
}
/**
 * @param string $message
 */
function ppError($message = "", $context = [])
{
    ppLog($message, $context = [],Logger::ERROR);
}
/**
 * @param $message
 * @return mixed|string
 */
function convertMsgToHumanReadable($message)
{
    if ($message instanceof Exception) {
        $e = $message;
        $exceptionMsg = get_class($e)
            . ": " . $e->getMessage() . "\n"
            . " File = " . $e->getFile() . " on line(" . $e->getLine() . ")\n"
            . $e->getTraceAsString() . "\n";

        return $exceptionMsg;
    } else {
        $message = print_r($message, true);
    }

    return $message;
}
/**
 * @param string|array $message
 */
function ppDebug($message = "")
{
    if (config('app.debug') !== true) {
        return;
    }

    $backTrace = debug_backtrace();

    $fileLines = [];

    foreach ($backTrace as $index => $caller) {

        $files = [];
        if (array_key_exists('file', $caller)) {
            $files = explode("/", $caller["file"]);
        } else if (array_key_exists('class', $caller)) {
            $files[] = $caller['class'];
        }
        $file = array_pop($files);

        $fileLogString = "{$file}::{$caller['function']}";
        $fileLogString .= (isset($caller['line'])) ? "({$caller['line']})" : "";
        $fileLines[] = $fileLogString;
    }

    $fileLine = implode("\n(from)", $fileLines);

    $logUuid = getLogUuid();
    $message = convertMsgToHumanReadable($message);
    $logger = createLog('debugLog',Logger::DEBUG);
    $allMessage = "({$logUuid}) {$fileLine} => {$message}\n";

    $logger->debug(json_encode($allMessage));
}

function createLog($name, $logLevel)
{
    static $loggers = [];
    $key = md5($name);
    if (!isset($loggers[$key])) {
        $loggers[$key] = new Logger($name);
        $logName = getLogName($name);

        $handler = new StreamHandler($logName, $logLevel);
        $handler->setFormatter(new CustomJsonFormatter()); // 套用自訂格式
        $loggers[$key]->pushHandler($handler);
    }

    // 如果只傳一個 name，回傳單個 logger，否則回傳陣列
    return $loggers[$key];
}
/**
 * @param string $message
 * @param mixed $context
 * @param int $logLevel
 */
function ppLog($message = "", $context = [], $logLevel = Logger::INFO)
{
        // if (is_object($message) || is_array($message)) {
        //     $message = json_encode($message);
        // }

    $logger = createLog('ppLog',$logLevel);

    // if ($logger !== null) {
    $backTrace = debug_backtrace();
    $caller = array_shift($backTrace);

    if ($logLevel <= Logger::INFO) {
        $arr = explode("/", $caller["file"]);
        $line = $caller['line'];
        $file = array_pop($arr);
        $message = convertMsg($message);

        if ((gettype($message) === "object") || (gettype($message) === "array")) {
            $logData = ['file' => $file, 'line' => $line, 'contentObj' => $message];
        } else {
            $logData = ['file' => $file, 'line' => $line, 'contentMsg' => $message];
        }

        $logger->log($logLevel, json_encode($logData),$context);
        ppDebug($message);

    } else {

        $caller = array_shift($backTrace);
        $line = $caller['line'];
        $arr = explode("/", $caller["file"]);
        $file = array_pop($arr);
        $message = convertMsg($message);

        $traces = [];
        foreach ($backTrace as $index => $caller) {
            $tempTraces['file'] = isset($caller['file']) ? $caller['file'] : null;
            $tempTraces['class'] = isset($caller['class']) ? $caller['class'] : null;
            $tempTraces['line'] = isset($caller['line']) ? $caller['line'] : null;

            $traces[] = $tempTraces;
        }

        if ((gettype($message) === "object") || (gettype($message) === "array")) {
            $logData = ['file' => $file, 'line' => $line, 'contentObj' => $message, 'traces' => $traces];
        } else {
            $logData = ['file' => $file, 'line' => $line, 'contentMsg' => $message, 'traces' => $traces];
        }

        $errorLogger = createLog('errorLog',$logLevel);
        $errorLogger->log($logLevel, json_encode($logData),$context);

        ppDebug($message);
    }
}

