<?php
/*
    2025/9/2 elfjane 新增
    功能：簡單帳號登入/登出
*/
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AuthController;

class LoginController extends AuthController
{
    private $accounts = [];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = env('AUTH_USER', "elfjane");        
        $password = env('AUTH_PASSWORD', "1234");  
        $this->accounts = [
            $user => $password,   // 帳號 => 密碼 (之後建議放 DB 或 env)
        ];
    }

    public function __default()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($this->accounts[$username]) && $this->accounts[$username] === $password) {
            $_SESSION['user'] = $username;
            return $this->set("success", "登入成功: {$username}");
        }

        return $this->set("error", "帳號或密碼錯誤");
    }

    public function logout()
    {
        unset($_SESSION['user']);
        session_destroy();
        return $this->set("success", "已登出");
    }

    public function status()
    {
        if (!empty($_SESSION['user'])) {
            return $this->set("success", "已登入: " . $_SESSION['user']);
        }
        return $this->set("error", "尚未登入");
    }
}
