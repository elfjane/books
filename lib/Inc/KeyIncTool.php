<?php
namespace Lib\Tools\Inc;
class KeyIncTool
{
    private $template = null;
    private $path = null;
    public $private_key_pem;
    public $public_key_pem;

    public $method = "sha256WithRSAEncryption";
    public $key_path = CRON_BASE_PATH_KEY;

	public function __construct()
	{
        $this->load_key();
	}

    public function load_key()
    {
        $this->private_key_pem = file_get_contents($this->key_path .'/private_key.pem');
        $this->public_key_pem  = file_get_contents($this->key_path .'/public_key.pem');
    }

    public function check_sign($data, $t_signature)
    {
        //$signature = base64_decode($t_signature);
        // 檢查是否為 16 進制數字
        if (!ctype_xdigit($t_signature)) {
            return false;
        }
        // 檢查是否為 正確的 16進制組合
        if (strlen($t_signature) % 2 != 0) {
            return false;
        }

        $signature = hex2bin(($t_signature));
        $r = openssl_verify($data, $signature, $this->public_key_pem, $this->method);
        return $r;
    }

    public function sign($data)
    {
        openssl_sign($data, $signature, $this->private_key_pem, "SHA256");
        return bin2hex(($signature));
//        return base64_encode($signature);
    }

    public function create_key()
    {
        $new_key_pair = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export($new_key_pair, $this->private_key_pem);

        $details = openssl_pkey_get_details($new_key_pair);
        $this->public_key_pem = $details['key'];

        $file_private = $this->key_path .'/private_key.pem';
        $file_public  = $this->key_path .'/public_key.pem';
        $time = time();

        if (file_exists($file_private)) {
            rename($file_private, $file_private .".". $time);
        }
        if (file_exists($file_public)) {
            rename($file_public, $file_public .".". $time);
        }

        file_put_contents($file_private, $this->private_key_pem);
        file_put_contents($file_public,  $this->public_key_pem);

    }
}
