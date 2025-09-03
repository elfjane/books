<?php
/*
    2025/8/21 elfjane test
*/
namespace App\Http\Controllers;
use Lib\Code\SmartyCode;

abstract class SmartyController extends SmartyCode
{
    protected $viewFilename;

    public function setView($filename)
    {
        $this->viewFilename = $filename;
    }
    public function getView()
    {
        return $this->viewFilename;
    }
}