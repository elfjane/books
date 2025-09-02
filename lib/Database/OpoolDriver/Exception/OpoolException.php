<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/12 17:08
 */

namespace Lib\Database\OpoolDriver\Exception;

class OpoolException extends \RuntimeException
{
    public $oraCode;
    public $description;

    /**
     * OpoolConnException constructor.
     * @param string $message
     * @param int $code
     * @param string $oraCode
     */
    public function __construct($message, $code = 0, $oraCode = "")
    {
        $this->oraCode = $oraCode;
        parent::__construct($message, $code, null);
    }

    /**
     * @throws OpoolException
     */
    public static function throwSqlIsNull()
    {
        throw new OpoolException("sql is null", OpoolExceptionCodeEnum::ERR_SQL_NULL);
    }

    /**
     * @throws OpoolException
     */
    public static function throwConnIsFetching()
    {
        throw new OpoolException("connection is fetching, can't query again", OpoolExceptionCodeEnum::ERR_FETCHING);
    }

    /**
     * @throws OpoolException
     */
    public static function throwSendSqlError()
    {
        throw new OpoolException("send SQL error", OpoolExceptionCodeEnum::ERR_SEND_SQL);
    }

    /**
     * @param $timeouts
     * @param $sql
     * @throws OpoolException
     */
    public static function throwQueryTimeout($timeouts, $sql)
    {
        throw new OpoolException(
            "Query time out [" . $timeouts . "s] [ " . $sql . " ]",
            OpoolExceptionCodeEnum::ERR_QUERY_TIMEOUT
        );
    }

    /**
     * @throws OpoolException
     */
    public static function throwConnectionError()
    {
        throw new OpoolException('opool can not connect!!', OpoolExceptionCodeEnum::ERR_CONNECTION);
    }
}
