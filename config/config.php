<?php
/**
 * config.php
 *
 * @package Model, View and Controller Framework
 * @filesource
 */

require_once 'config_global.php';

/**
 * CRON_BASE_PATH
 *
 * Dynamically figure out where in the filesystem we are located.
 * @global string WEB_BASE_PATH Absolute path to our framework
 */

define('CRON_BASE_PATH', dirname(__DIR__));
define('CRON_BASE_PATH_KEY', "key");

// 2020/4/13 elfjane
define('MYSQL_CHARACTER_SET', "SET NAMES 'utf8mb4'");

// 2014/9/11 elfjane
define('MYSQL_HOST_MAIN', '172.16.101.107');
define('MYSQL_MAIN_USER', 'root');
define('MYSQL_MAIN_PASSWD', '1234');
define('MYSQL_MAIN_DATABASE', 'test');

// 2025/5/4 elfjane
define('REDIS_HOST', '172.16.2.8');
define('REDIS_HOST_KEY_TOKEN', 'token_');

define('CRON_DEBUG_MODE', 1);
define('CRON_DEBUG_DB_MODE', 1);
define('CRON_DEBUG_MODE_TIME', 1);
define('CRON_DEBUG_PAYMENT_MODE', 1);
define('CRON_CHANGE_IP', 0);

define('CRON_ADMIN_URL', 'http://s-sdk-prod-admin.elfjane.com/app/');
define('CRON_WEB_URL', 'http://s-sdk-prod-api.elfjane.com/app/');
define('CRON_SERVICE_URL', 'http://s-sdk-prod-service.elfjane.internal/app');
define('CRON_PAYMENT_URL', 'http://s-sdk-prod-payment.elfjane.internal/app/');

// QQ OpenAPIs
define('CRON_QQ_GET_AUTH_CODE_URL', 'https://graph.qq.com/oauth2.0/authorize');
define('CRON_QQ_GET_ACCESS_TOKEN_URL', 'https://graph.qq.com/oauth2.0/token');
define('CRON_QQ_GET_OPENID_URL', 'https://graph.qq.com/oauth2.0/me');

// WeChat APIs
define('CRON_WECHAT_VERIFY_TOKEN_URL', 'https://api.weixin.qq.com/sns/auth');

$test_server_ip_address = array('172.22.60.36');

//Google Play Payment Sandbox mode, 1=sandbox ,0=prod
define('GAME_PAYMENT_ANDROID_IS_SANDBOX', '0');
