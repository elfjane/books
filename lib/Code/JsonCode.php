<?php
namespace Lib\Code;
abstract class JsonCode extends ToolCode {


    public $request;
    public function __construct() {
        parent::__construct();
        $this->display = "json";
    }
}
