<?php
namespace Lib\Tools;

class ConfigTool
{
    protected static $configs = [];

    public static function get(string $key, $default = null)
    {
        // key 可能是 "database.host"
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        // 如果還沒載入，去讀 config/file.php
        if (!isset(self::$configs[$file])) {
            $path = __DIR__ . '/../../config/' . $file . '.php';
            if (file_exists($path)) {
                self::$configs[$file] = require $path;
            } else {
                self::$configs[$file] = [];
            }
        }

        // 逐層取值
        $value = self::$configs[$file];
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }
        return $value;
    }
}
