<?php
/*
    2025/8/28 elfjane 新增
    功能：透過 ?file=name 讀取檔案
           - 文字檔回傳內容 (json 格式)
           - 圖片檔直接輸出
           - html/htm/php 直接輸出成 text/html
           - 僅允許特定副檔名
*/
namespace App\Http\Controllers\File;

use App\Http\Controllers\AuthController;

class ReadFileContentController extends AuthController
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
        
        $file = $_GET['file'] ?? '';

        if (empty($file)) {
            return $this->set("error", "未指定檔案名稱");
        }

        $basePath = env('FILE_PATH', "/var/www/html");

        // 防止路徑穿越
        $filePath = realpath($basePath . '/' . ltrim($file, '/'));
        if ($filePath === false || strpos($filePath, realpath($basePath)) !== 0) {
            return $this->set("error", "非法檔案路徑");
        }

        if (!is_file($filePath)) {
            return $this->set("error", "檔案不存在: {$file}");
        }

        // 檢查副檔名是否允許
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
            return $this->set("error", "不允許的檔案副檔名: {$ext}");
        }

        // 判斷檔案類型
        $mimeType = mime_content_type($filePath);
        $imageTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

        if (in_array($mimeType, $imageTypes)) {
            // 圖片直接輸出
            header("Content-Type: $mimeType");
            readfile($filePath);
            exit;
        }

        if (in_array($ext, ['html', 'htm'])) {
            // HTML 檔案直接輸出
            header("Content-Type: text/html; charset=utf-8");
            readfile($filePath);
            exit;
        }

        if ($ext === 'php') {
            // PHP 檔案：先執行，再輸出成 HTML
            header("Content-Type: text/html; charset=utf-8");
            ob_start();
            include $filePath;   // 這裡會執行 PHP
            $output = ob_get_clean();
            echo $output;
            exit;
        }

        // 文字檔 (json 回傳)
        $content = file_get_contents($filePath);

        $this->set("file", [
            "name" => basename($filePath),
            "size" => filesize($filePath),
            "mtime" => date("Y-m-d H:i:s", filemtime($filePath)),
            "content" => $content
        ]);

        $this->setAccept();
    }
}
