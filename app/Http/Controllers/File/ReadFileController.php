<?php
/*
    2025/8/28 elfjane 新增
    功能：讀取目錄檔案，僅回傳指定副檔名
*/
namespace App\Http\Controllers\File;

use App\Http\Controllers\AuthController;

class ReadFileController extends AuthController
{
    // 允許的副檔名 (小寫，不含點)
    protected $allowedExtensions = [
        'txt', 'md', 'json',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'html', 'htm',
        'php',
    ];

    public function __default()
    {
        // 檢查登入
        if (empty($_SESSION['user'])) {
            return $this->set("error", "請先登入");
        }        
        $dir = $_GET['dir'] ?? '';

        // 基本路徑
        $basePath = env('FILE_PATH', "/var/www/html");
        $path = rtrim($basePath, '/') . ($dir ? '/' . trim($dir, '/') : '');

        if (!is_dir($path)) {
            return $this->set("error", "指定的路徑不存在或不是目錄: {$path}");
        }

        // 取得清單
        $items = scandir($path);

        $files = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . '/' . $item;

            if (is_file($fullPath)) {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));

                // 過濾副檔名
                if (!in_array($ext, $this->allowedExtensions)) {
                    continue;
                }

                $files[] = [
                    'name'  => $item,
                    'size'  => filesize($fullPath),
                    'mtime' => date("Y-m-d H:i:s", filemtime($fullPath)),
                ];
            }
        }

        $this->set("files", $files);
        $this->setAccept();
    }
}
