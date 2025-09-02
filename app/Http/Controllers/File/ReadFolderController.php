<?php
/*
    2025/8/21 elfjane test
*/
namespace App\Http\Controllers\File;
use App\Http\Controllers\AuthController;

class ReadFolderController extends AuthController
{

    public function __default()
    {
        // 檢查登入
        if (empty($_SESSION['user'])) {
            return $this->set("error", "請先登入");
        }        

        $dir = $_GET['dir'];

        if (empty($dir)) {
            $path = env('FILE_PATH',"/var/www/html");
        } else {
            $path = env('FILE_PATH',"/var/www/html") . '/' . $dir;
        }
        // 取得目錄清單
        $items = scandir($path);

        // 過濾只留下子目錄
        $dirs = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (is_dir($path . '/' . $item)) {
                $dirs[] = $item;
            }
        }

        $this->set("dirs",       $dirs);

        $this->setAccept();
    }
}
