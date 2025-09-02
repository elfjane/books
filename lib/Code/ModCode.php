<?php
namespace Lib\Code;
abstract class ModCode
{

    public    $display  = "json";
    public    $classes  = NULL;
    protected $mLog     = NULL;
    protected $mTrn1    = NULL;
    protected $mMain    = NULL;

	public function __construct()
	{

	}

    abstract public function __default();
}
