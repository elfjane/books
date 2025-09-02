<?php
/*
    2025/9/2 elfjane 新增
    功能：簡單帳號登入/登出
*/
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AuthController;

class LogoutController extends AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function __default()
    {
        unset($_SESSION['user']);
        session_destroy();
        return $this->set("success", "已登出");        
    }
}
