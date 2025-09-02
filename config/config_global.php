<?php
/**
 * config.php
 *
 * @package Model, View and Controller Framework
 * @filesource
 */

ini_set('max_execution_time', 0);
ini_set('magic_quotes_gpc', 'on');
//date_default_timezone_set("Asia/Taipei");
date_default_timezone_set("UTC");
define('CRON_LOG_TIMEZONE', "Asia/Taipei");

/**
 * CRON_BASE_PATH
 *
 * Dynamically figure out where in the filesystem we are located.
 * @global string WEB_BASE_PATH Absolute path to our framework
 */

define('CRON_BASE_MD5_KEY', "Ye72#8n_4fqv5z93");
define('CRON_BASE_DATE', "Y-m-d H:i:s");
define('CRON_BASE_LOG_DATE', "Ymd");
define('CRON_SERVER_MODE', 'prod');  // 伺服器狀態


define('DATABASE_STATUS_ACCEPT', 1);
define('DATABASE_STATUS_FREEZE', 10);

define('UID_TOKEN_STEP_CHECKED', 10);
define('UID_TOKEN_STEP_LOGIN', 20);

if (isset($_SERVER['X_DEVELOPMENT'])) {
    define('CRON_DEVELOPMENT',   $_SERVER['X_DEVELOPMENT']);
} else {
    define('CRON_DEVELOPMENT',   'prod');
}

define('JWT_SECRET_KEY', 'k25!LNd9HM4A@167');
define('JWT_TIME_EXPIRED', 94608000);
define('JWT_FULL_CHECK', 1);

define('G_OPOOL', '172.16.101.107:11340');
define('G_OPOOL_LONG', '172.16.101.107:11340');
