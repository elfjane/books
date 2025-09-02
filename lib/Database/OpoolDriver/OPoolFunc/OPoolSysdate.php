<?php

namespace Lib\Database\OpoolDriver\OPoolFunc;


class OPoolSysdate extends OPoolFuncBase
{
    //private $days;
    private $isTrunc;
    private $seconds;

    public function __construct($days = 0, $isTrunc = false)
    {
        //$this->days = $days;
        $this->seconds = $days * 24 * 60 * 60;

        $this->isTrunc = $isTrunc;

    }

    public function setSeconds($seconds)
    {
        $this->seconds = $seconds;
    }

    public function getSql()
    {
        if ($this->isTrunc) {
            return "trunc(sysdate) + ((1 / 24 / 60 / 60) * {$this->seconds})";
        }

        return "sysdate + ((1 / 24 / 60 / 60) * {$this->seconds})";
    }
}