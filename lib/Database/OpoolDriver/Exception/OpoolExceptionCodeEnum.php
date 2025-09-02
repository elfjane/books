<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/12 17:08
 */

namespace Lib\Database\OpoolDriver\Exception;

abstract class OpoolExceptionCodeEnum
{
    const ERR_SEND_SQL = 1000;
    const ERR_FETCHING = 1001;
    const ERR_SQL_NULL = 1002;
    const ERR_QUERY_TIMEOUT = 1003;
    const ERR_CONNECTION = 1004;
}
