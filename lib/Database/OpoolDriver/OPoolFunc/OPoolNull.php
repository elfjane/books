<?php

namespace Lib\Database\OpoolDriver\OPoolFunc;

class OPoolNull extends OPoolFuncBase
{
    public function getSql()
    {
        return "null";
    }
}