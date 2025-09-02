<?php
/*
    2025/9/2 elfjane 新增
    功能：index
*/
namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function __default()
    {
        $this->set("success", "登入成功: {$username}");
    }
}
