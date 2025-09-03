<?php
namespace Lib\Code;
abstract class SmartyCode extends ToolCode {


    public $request;
    public function __construct() {
        parent::__construct();
        $this->display = "smarty";
    }
}
