<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/12 17:08
 */

namespace Lib\Database\OpoolDriver\Enums;

abstract class OpoolCommitStateEnum
{
    const COMMIT_ON = 0;
    const COMMIT_OFF = 1;
    const WAIT_TIME = 35;
    const SQL_ERR = "--BEGIN ERR--\r\n";
    const SQL_ERR_END = "--END--\r\n";
    const SQL_BEGIN = "--\1\2BEGIN--";
    const SQL_END = "==\1\2===\r\n";
    const SQL_ROW = "##\1\2##\r\n";
    const SQL_COL = "\1\1\2\t";
    const SQL_EDIT_OK = "OK\r\n";
    const SQL_SERVER_ERR = "(ORA-03113|ORA-12541|ORA-03114|ORA-01034|ORA-27101)";
    const SQL_INPUT = "\r\n--INPUT END--\r\n";
    const ROW_BUFFER = 102400;
}