<?php
/*
    2025/9/2 elfjane 新增
    功能：index
*/
namespace App\Http\Controllers;

class IndexController extends SmartyController
{
    public function __default()
    {
        $this->set('abc', 666);
        $this->setView('index');
        $this->setAccept();
    }
}
