<?php
namespace Lib\Code;
use Exception;
abstract class JsonAuthCode extends JsonCode {

    public $authorization; // 認證登入用
    public $jwtTools; // 認證相關資料

    protected $jwtData;
    protected $headers;

    public function __construct()
    {
        parent::__construct();
        $this->jwtTools = new JwtCode();
        $this->jwtData = $this->getAuthToken(JWT_FULL_CHECK);
    }

    // token 認證
    public function getJwtData($key)
    {
        return $this->jwtData[$key];
    }

    // token 認證
    public function setAuthToken()
    {
        $this->headers       = array('authorization');
        $this->setHeaderData($this->headers);
    }

    // token 認證
    public function getAuthToken($isFullCheck = false)
    {
        $this->setAuthToken();
        $authorization = $this->headerData['authorization'];
        //var_dump($authorization);

        $isValid = $this->jwtTools->isValid($authorization, $isFullCheck);
        // 驗證
        if (empty($isValid)) {
            throw new Exception('Token 無效或已過期', 11101);
        }
        // 將資料放在 sdk 全域下
        $this->authorization = $authorization;
        $jwtData = $this->jwtTools->getPayload($authorization);
        return $jwtData;
    }

}
