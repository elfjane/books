<?php
namespace Lib\Code;
use Exception;
abstract class JsonAuthCode extends JsonCode {

    public $authorization; // �{�ҵn�J��
    public $jwtTools; // �{�Ҭ������

    protected $jwtData;
    protected $headers;

    public function __construct()
    {
        parent::__construct();
        $this->jwtTools = new JwtCode();
        $this->jwtData = $this->getAuthToken(JWT_FULL_CHECK);
    }

    // token �{��
    public function getJwtData($key)
    {
        return $this->jwtData[$key];
    }

    // token �{��
    public function setAuthToken()
    {
        $this->headers       = array('authorization');
        $this->setHeaderData($this->headers);
    }

    // token �{��
    public function getAuthToken($isFullCheck = false)
    {
        $this->setAuthToken();
        $authorization = $this->headerData['authorization'];
        //var_dump($authorization);

        $isValid = $this->jwtTools->isValid($authorization, $isFullCheck);
        // ����
        if (empty($isValid)) {
            throw new Exception('Token �L�ĩΤw�L��', 11101);
        }
        // �N��Ʃ�b sdk ����U
        $this->authorization = $authorization;
        $jwtData = $this->jwtTools->getPayload($authorization);
        return $jwtData;
    }

}
