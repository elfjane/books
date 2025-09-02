<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/27
 */

namespace Lib\Utils;

/**
 * 字串相關處理.
 *
 * Class StrUtil
 * @package Lib\Utils
 */
class StrUtil
{
    /**
     * 將指定字串轉換為小寫駝峰式字串表示.
     *
     * Example:
     * ```php
     * $str = new PCPayStr();
     * $myStr = "I_am_a_handsome_boy";
     * $camelStr = $str->lowerCamelize($myStr);
     *
     * echo $camelStr
     * // iAmAHandsomeBoy
     * ```
     *
     * @param string $input 輸入字串
     * @param string $separator 原字串單字切分符號
     * @return string
     */
    public function lowerCamelize($input, $separator = '_')
    {
        $inputs = explode($separator, $input);

        $camelString = '';
        foreach ($inputs as $input) {
            $camelString .= ucfirst($input);
        }

        return lcfirst($camelString);
    }

    /**
     * 將指定字串轉換為大寫駝峰式字串表示.
     *
     * Example:
     * ```php
     * $str = new PCPayStr();
     * $myStr = "I am a handsome boy";
     * $camelStr = $str->lowerCamelize($myStr, " ");
     *
     * echo $camelStr;
     * // IAmAHandsomeBoy
     * ```
     *
     * @param string $input 輸入字串
     * @param string $separator 原字串單字切分符號
     * @return string
     */
    public function upperCamelize($input, $separator = '_')
    {
        $inputs = explode($separator, $input);

        $camelString = '';
        foreach ($inputs as $input) {
            $camelString .= ucfirst($input);
        }

        return $camelString;
    }

    /**
     * 將大寫駝峰式字串轉換為指定字串表示
     *
     * @param $input
     * @param string $separator
     * @return string
     */
    public static function convertCamelToUpper($input, $separator = '_')
    {
        $input = ucfirst($input);
        return strtoupper(preg_replace('/([^A-Z_])([A-Z])/', "$1{$separator}$2", $input));
    }

    /**
     * 將小寫駝峰式字串轉換為指定字串表示
     *
     * @param $input
     * @param string $separator
     * @return string
     */
    public function convertCamelToLower($input, $separator = '_')
    {
        $input = ucfirst($input);
        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1{$separator}$2", $input));
    }

    /**
     * 驗証是否為 JSON 字串.
     *
     * Example:
     * ```php
     * $json = '{"name":"Eric"}';
     *
     * echo $json
     * // true
     * ```
     *
     * @param mixed $json 要驗証的變數
     * @return bool
     */
    public function isJson($json)
    {
        if (is_object($json) || is_array($json)) {
            return false;
        }

        return is_array(json_decode($json, true));
    }
}