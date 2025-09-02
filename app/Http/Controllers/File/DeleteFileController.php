<?php
/*
    2025/9/2 elfjane 修改
    功能：透過 ?file=name 將檔案搬移到回收區 (FILE_PATH_DELETE)
           - 僅允許特定副檔名
           - 保留原始目錄結構
*/
namespace App\Http\Controllers\File;

use App\Http\Controllers\AuthController;

class DeleteFileController extends AuthController
{
    // 允許搬移的副檔名 (小寫，不含點)
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
            return $this->set("error", "請先登入後再刪除檔案");
        }
        
        $file = $_GET['file'] ?? '';

        if (empty($file)) {
            return $this->set("error", "未指定檔案名稱");
        }

        $basePath = env('FILE_PATH', "/var/www/html");
        $deletePath = env('FILE_PATH_DELETE', "/var/www/html/trash");

        // 確保回收區目錄存在
        if (!is_dir($deletePath)) {
            mkdir($deletePath, 0755, true);
        }

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
            return $this->set("error", "不允許搬移的檔案副檔名: {$ext}");
        }

        // 計算相對路徑
        $relativePath = ltrim(str_replace(realpath($basePath), '', $filePath), '/');

        // 生成回收區完整目錄
        $destDir = dirname($deletePath . '/' . $relativePath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // 生成回收區完整檔案路徑
        $destFile = $deletePath . '/' . $relativePath;

        // 避免檔案名稱重複
        $counter = 1;
        while (file_exists($destFile)) {
            $destFile = $destDir . '/' . pathinfo($filePath, PATHINFO_FILENAME) . "_{$counter}." . $ext;
            $counter++;
        }

        // 搬移檔案
        if (@rename($filePath, $destFile)) {
            $this->set("success", "檔案已搬移到回收區: " . str_replace($deletePath.'/', '', $destFile));
        } else {
            $this->set("error", "檔案搬移失敗: " . basename($filePath));
        }

        $this->setAccept();
    }
}
