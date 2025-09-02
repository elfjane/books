<?php
/*
    2025/8/28 elfjane 修改
    功能：讀取目錄檔案，僅回傳指定副檔名
          - 若同目錄下有 filelist.help，使用裡面每行的名稱對應檔案顯示 displayName
          - 檔案依 name 排序
*/
namespace App\Http\Controllers\File;

use App\Http\Controllers\AuthController;

class ReadFileDisplayController extends AuthController
{
    protected $allowedExtensions = [
        'txt', 'md', 'json',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'html', 'htm',
        'php',
    ];

    public function __default()
    {
        if (empty($_SESSION['user'])) {
            return $this->set("error", "請先登入");
        }        

        $dir = $_GET['dir'] ?? '';
        $basePath = env('FILE_PATH', "/var/www/html");
        $path = rtrim($basePath, '/') . ($dir ? '/' . trim($dir, '/') : '');

        if (!is_dir($path)) {
            return $this->set("error", "指定的路徑不存在或不是目錄: {$path}");
        }

        // ===== 讀取 filelist.help =====
        $helpFile = $path . '/filelist.help';
        $displayMap = [];
        if (is_file($helpFile)) {
            $lines = file($helpFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                [$fileName, $displayName] = array_pad(explode(",", $line, 2), 2, '');
                if ($fileName !== '') {
                    $displayMap[$fileName] = $displayName ?: $fileName;
                }
            }
        }

        $items = scandir($path);
        $files = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $path . '/' . $item;
            if (!is_file($fullPath)) continue;

            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedExtensions)) continue;

            $displayName = $displayMap[$item] ?? $item;

            $files[] = [
                'name'        => $item,
                'displayName' => $displayName,
                'size'        => filesize($fullPath),
                'mtime'       => date("Y-m-d H:i:s", filemtime($fullPath)),
            ];
        }

        // ===== 依 name 排序 =====
        usort($files, function($a, $b){
            return strcasecmp($a['name'], $b['name']);
        });

        $this->set("files", $files);
        $this->setAccept();
    }
}
