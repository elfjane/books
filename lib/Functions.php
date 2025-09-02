<?php
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
       $headers = array ();
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}
if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $name = 'F_' . trim($key);
        if (!isset($_ENV[$name])) {
            return $default;

        }
        return $_ENV[$name];
    }
}
function loadEnv($path = '.env')
{
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = 'F_'.trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function wLog($filename, $logData)
{
    date_default_timezone_set(CRON_LOG_TIMEZONE);
    if (is_array($logData)) {
        $data = array_merge(['datetime' => date(CRON_BASE_DATE)], $logData);
    } else {
        $data = [
            'datetime' => date(CRON_BASE_DATE),
            'log' => $logData
        ];
    }

    $logStr = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    $datefilename = date(CRON_BASE_LOG_DATE);

    $dateFolder = date(CRON_BASE_LOG_DATE); // 建立如 logs/20250507/
    $dateFile   = date(CRON_BASE_LOG_DATE); // 如 20250507_140501
    $dirPath    = CRON_BASE_PATH . "/logs/{$dateFolder}";
    $filePath   = "{$dirPath}/{$filename}_{$dateFile}.txt";

    // 建立資料夾（如果不存在）
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0777, true); // 第三參數 true 表示遞迴建立多層資料夾
    }
    file_put_contents($filePath, $logStr, FILE_APPEND);
}
