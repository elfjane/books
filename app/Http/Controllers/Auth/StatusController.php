<?php
/*
    2025/9/2 elfjane 新增
    功能：簡單帳號登入/登出
*/
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AuthController;

class StatusController extends AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function __default()
    {
        if (!empty($_SESSION['user'])) {
            return $this->set("success", "已登入: " . $_SESSION['user']);
        }
        return $this->set("error", "尚未登入");
    }
}
