<?php
namespace Lib\Code;

use Lib\Tools\RedisTool;
use Exception;

class JwtCode
{
    private $secret;
    private $payload;

    protected $expectedSignature;
    protected $signature;
    protected $authorization;

    public function __construct()
    {
        $this->secret = JWT_SECRET_KEY;
    }

    public function set($name, $value)
    {
        $this->payload[$name] = $value;
    }

    public function get($name)
    {
        if (!isset($this->payload[$name])) {
            return null;
        }
        $data = $this->payload[$name];
        return $data;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function generate(array $data): string
    {
        $payload = $data;
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_TIME_EXPIRED;
        //$payload['jti'] = bin2hex(random_bytes(16)); // 加入唯一 ID
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $headerEncoded = $this->base64urlEncode(json_encode($header));
        $payloadEncoded = $this->base64urlEncode(json_encode($payload));
        $signatureSha256 = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);

        $this->signature = $this->base64urlEncode($signatureSha256);
        $this->authorization = "$headerEncoded.$payloadEncoded.$this->signature";

        return $this->authorization;
    }

    public function checkTokenValid($uid, $signature)
    {
        $redis = new RedisTool();

        $token = $redis->getToken($uid);
        if ($signature != $token) {
            throw new Exception('valid fail', 220201);
        }
    }

    public function getValid(string $jwt, $isFullCheck = false): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception('create user fail', 220101);
        };

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $this->expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "$headerB64.$payloadB64", $this->secret, true)
        );

        if (!hash_equals($this->expectedSignature, $signatureB64)) {
            throw new Exception('valid fail', 220111);
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $this->payload = json_decode($payloadJson, true);
        if ($isFullCheck) {
            $uid = $this->payload['uid'];
            $this->checkTokenValid($uid, $this->expectedSignature);
        }
        $this->payload['signature'] = $this->expectedSignature;
        return $this->payload;
    }


    public function isValid(string $jwt, $isFullCheck = false): array
    {
        $payload = $this->getValid($jwt, $isFullCheck);

        // 檢查過期時間
        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new Exception('valid expired', 220121);
        }

        return $payload;
    }

    public function getPayload(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        return json_decode($this->base64UrlDecode($parts[1]), true);
    }
}
