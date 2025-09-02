<?php
namespace Lib\Code;
use Lib\Tools\Inc\KeyIncTool;
use Exception;
abstract class ToolCode extends ModCode
{
    public $_mUrl        = CRON_ADMIN_URL;
    public $_mAdUrl      = CRON_PAYMENT_URL;
    public $_webUrl      = CRON_WEB_URL;
    public $_mServiceUrl = CRON_SERVICE_URL;
    public $_mPaymentUrl = CRON_PAYMENT_URL;
    public $displayData  = array();
    public $requestData  = array();
    public $headerData  = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function set($var, $val)
    {
        return $this->displayData[$var] = $val;
    }

    public function get($var, $val)
    {
        return $this->displayData[$var];
    }

    public function getRemoteIP()
    {
        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $user_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (isset($user_ip[0])) {
                $ip = $user_ip[0];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip;
    }

    public function setRemoteIP($name)
    {
        if (CRON_CHANGE_IP == 0) {
            return;
        }
        switch ($name) {
            case 'ip': {
                if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $this->requestData[$name] = $_SERVER['REMOTE_ADDR'];
                } else {
                    $user_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    if (isset($user_ip[0])) {
                        $this->requestData[$name] = $user_ip[0];
                    } else {
                        $this->requestData[$name] = $_SERVER['REMOTE_ADDR'];
                    }
                }
            } break;
        }
    }

    public function setRequestData($data, $error = true)
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $_REQUEST = json_decode($input, true) ?? [];
        }
        foreach ($data as $row) {
            if (isset($_REQUEST[$row])) {
                $this->requestData[$row] = htmlspecialchars($_REQUEST[$row]);
            } else {
                if ($error) {
                    throw new Exception('lost parameter ' . $row, 10002);
                }
            }
            $this->setRemoteIP($row);
        }
    }

    public function setRequestMD5Data($data, $tkey = 'key')
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $_REQUEST = json_decode($input, true) ?? [];
        }
        if (!isset($_REQUEST[$tkey])) {
            throw new Exception('no key', 10001);
        }
        $md5_data = '';
        foreach ($data as $row) {
            if (isset($_REQUEST[$row])) {
                $this->requestData[$row] = htmlspecialchars($_REQUEST[$row]);
                $md5_data .= $_REQUEST[$row];
            } else {
                throw new Exception('lost parameter2 ' . $row, 10002);
            }
        }

        $this->requestData[$tkey] = $_REQUEST[$tkey];
        $key                      = $_REQUEST[$tkey];
        $md5_key                  = md5($md5_data . CRON_BASE_MD5_KEY);
        //echo $md5_key;
        //exit;
        if ($md5_key != $key) {
            throw new Exception('key fail', 10001);
        }
        foreach ($data as $row) {
            $this->setRemoteIP($row);
        }
    }

    public function setRequestMD5SignData($data, $game_key, $tkey = 'sign')
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $_REQUEST = json_decode($input, true) ?? [];
        }
        ksort($data);
        if (!isset($_REQUEST[$tkey])) {
            throw new Exception('no key', 10001);
        }
        $md5_data = '';
        foreach ($data as $row) {
            if (isset($_REQUEST[$row])) {
                $this->requestData[$row] = htmlspecialchars($_REQUEST[$row]);
                $md5_data .= $_REQUEST[$row];
            } else {
                throw new Exception('lost parameter2 ' . $row, 10002);
            }
        }

        $this->requestData[$tkey] = $_REQUEST[$tkey];
        $key                      = $_REQUEST[$tkey];
        $md5_key                  = md5($md5_data . $game_key);

        if ($md5_key != $key) {
            throw new Exception('key fail', 10001);
        }
        foreach ($data as $row) {
            $this->setRemoteIP($row);
        }
    }

    public function makeMD5Data($data)
    {
        $md5_data = '';
        foreach ($data as $key => $val) {
            $md5_data .= $val;
        }
        $md5_key = md5($md5_data . CRON_BASE_MD5_KEY);

        return $md5_key;
    }

    public function get_IP()
    {
        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $user_ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $user_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $user_ip = $user_ip[0];
        }

        return $user_ip;
    }

    public function curl_get($url, $data = null, $headers = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 如果是 POST，帶上資料
        if (isset($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // 如果有 headers，設置 headers
        if (!empty($headers) && is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $html = curl_exec($ch);
        curl_close($ch);

        return $html;
    }

    public function GetJson($url, $data)
    {
        $result = json_decode($this->curl_get($url, http_build_query($data)), true);
        //        var_dump($result);
        //        echo $result["succ"];
        if (!isset($result["succ"]) || $result["succ"] !== 1) {
            throw new Exception($result["errorMsg"], $result["succ"]);
        }

        return $result;
    }

    public function check_sign($t_data, $sign, $error = true)
    {
        $key   = new KeyIncTool();
        $sdata = json_encode($t_data);
        if (!$key->check_sign($sdata, $sign)) {
            if ($error == false) {
                return false;
            }
            throw new Exception("sign check error", 10005);
        }

        return true;
    }

    public function setAccept()
    {
        $this->set('succ', 1);
    }

    public function check_user_token($data, $host = CRON_ADMIN_URL, $url = '/user_token')
    {
        $url    = $host . "/" . $url;
        $result = json_decode($this->curl_get($url, http_build_query($data)), true);
        if (!isset($result)) {
            throw new Exception("sign check error", 10005);
        }
        if (!isset($result["succ"]) || $result["succ"] !== 1) {
            $this->set('succ', $result['succ']);
            $this->set('errorMsg', $result['errorMsg']);
        }
        $this->set('succ', $result['succ']);

        return $result;
    }

    public function web_curl($url = null, $data = null, $headers = null)
    {
        if (empty($data)) {
            $data = $this->make_data_array();
        }
        if (empty($headers)) {
            $headers = $this->makeHeadersArray();
        }
        if ($url == null) {
            $url = get_called_class();
        }

        // 假設這是你的 cURL 函式調用
        $response = $this->curl_get($this->_mUrl . $url, http_build_query($data), $headers);

        // 解析 JSON 字串
        $result = json_decode($response, true);
        // 檢查是否解碼成功
        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            // 顯示錯誤，這裡也可以選擇記錄或其他處理
            $data = [
                'datetime' => date(CRON_BASE_DATE),
                'url' => $this->_mUrl . $url,
                'data' => $data,
                'headers' => $headers,
                'response' => $response,
            ];
            file_put_contents(CRON_BASE_PATH . '/logs/send_10004_error_' . date(CRON_BASE_LOG_DATE) . '.txt', json_encode($data) . "\r\n", FILE_APPEND);
            throw new Exception("json decode error", 10004);
        }

        if (isset($result["succ"]) && $result["succ"] != 1) {
            throw new Exception($result["errorMsg"], $result["succ"]);
        }

        return $result;
    }

    public function make_data_array()
    {
        if (!isset($this->request) || !is_array($this->request)) {
            return [];
        }

        $data = array();
        foreach ($this->request as $key) {
            if (isset($this->requestData[$key])) {
                $data[$key] = $this->requestData[$key];
            }
        }

        return $data;
    }

    public function makeHeadersArray()
    {
        $data = array();
        if (!isset($this->headers)) {
            return $data;
        }
        foreach ($this->headers as $key) {
            if (isset($this->headerData[$key])) {
                $data[] = $key. ": ".$this->headerData[$key];
            }
        }

        return $data;
    }

    public function get_data_array($data_array)
    {
        $data = array();
        foreach ($data_array as $key) {
            if (isset($this->requestData[$key])) {
                $data[$key] = $this->requestData[$key];
            }
        }

        return $data;
    }

    public function makePasswdMD5($passwd)
    {
        return md5($passwd . CRON_BASE_MD5_KEY);
    }

    public function log_data($data, $filename = 'test')
    {
        $logdata = "[" . date(CRON_BASE_DATE) . "] " . $data . "\r\n";
        file_put_contents(CRON_BASE_PATH . '/logs/send_' . $filename . '_' . date(CRON_BASE_LOG_DATE) . '.txt', $logdata, FILE_APPEND);
    }

    public function isSandbox()
    {
        if (CRON_SERVER_MODE == 'sandbox') {
            return true;
        } else {
            return false;
        }
    }

    public function get_token($len = 16)
    {
        return bin2hex(openssl_random_pseudo_bytes($len));
    }

    public function checkHostName()
    {
        $server_name = $_SERVER["SERVER_NAME"];
        if ($server_name == "sdk-test.miiann.com") {
            return "test";
        } elseif ($server_name == "sdkadmin-test.miiann.com") {
            return "test";
        } else {
            return "sdk";
        }
    }

    // log_data 必須為array
    public function log_file_json($log_data, $filename)
    {
        file_put_contents(CRON_BASE_PATH . '/logs/' . $filename . '_' . date(CRON_BASE_LOG_DATE) . '.txt', json_encode($log_data) . "\r\n", FILE_APPEND);
    }

    public function log_file_text($log, $filename)
    {
        file_put_contents(CRON_BASE_PATH . '/logs/' . $filename . '_' . date(CRON_BASE_LOG_DATE) . '.txt', $log . "\r\n", FILE_APPEND);
    }

    public function is_json($json_data)
    {
        json_decode($json_data);
        if (json_last_error() == JSON_ERROR_NONE) {
            return 1;
        }

        return 0;
    }

    public function getallheaders()
    {
        $resData = getallheaders();
        $data = [];
        if (empty($resData)) {
            return [];
        }
        foreach ($resData as $key => $val) {
            $lowerKey = strtolower($key);
            $data[$lowerKey] = $val;
        }
        unset($resData);
        return $data;
    }

    public function setHeaderData($data, $error = true)
    {
        $getHeaderData = $this->getallheaders();
        //var_dump($getHeaderData);
        foreach ($data as $row) {
            if (isset($getHeaderData[$row])) {
                $this->headerData[$row] = htmlspecialchars($getHeaderData[$row]);
            } else {
                if ($error) {
                    throw new Exception('lost header ' . $row, 10003);
                }
            }
        }
    }
}
