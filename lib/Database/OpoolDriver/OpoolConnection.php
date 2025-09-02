<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/12 17:08
 */

namespace Lib\Database\OpoolDriver;

use Lib\Database\OpoolDriver\Enums\OpoolCommitStateEnum;
use Lib\Database\OpoolDriver\Exception\OpoolException;

class OpoolConnection
{
    public $resource;
    public $sql;
    public $id;
    public $backup;
    public $ip;
    public $fetching = false;
    public $env;
    public $colname;
    public $commitState;
    public $colnums;
    public $isBreak;
    public $isBroken;
    public $port;
    public $description;
    public $bindinfo;
    public $inTransaction;
    private $affectedRows;

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->ip . ":" . $this->port;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function setCommitOn()
    {
        socket_set_timeout($this->resource, OpoolCommitStateEnum::WAIT_TIME);

        if (!fputs($this->resource, "set commit on\r\n")) {
            throw new \Exception("send commit off ERR");
        }
        $result = fgets($this->resource, 1024);
        if ($result != "auto commit on\r\n") {
            throw new \Exception("set commit off ERR : $result");
        }

        $this->commitState = OpoolCommitStateEnum::COMMIT_ON;
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function startTransaction()
    {
        $this->setCommitOff();
        $this->query("set transaction READ WRITE");
        $this->freeResult();
        $this->inTransaction = true;
    }

    /**
     * @throws \Exception
     */
    public function commit()
    {
        $this->query("commit");
        $this->freeResult();
        $this->setCommitOn();
        $this->inTransaction = false;
    }

    /**
     * @throws \Exception
     */
    public function rollback()
    {
        $this->query("rollback");
        $this->freeResult();
        $this->setCommitOn();
        $this->inTransaction = false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function setCommitOff()
    {
        socket_set_timeout($this->resource, OpoolCommitStateEnum::WAIT_TIME);

        if (!fputs($this->resource, "set commit off\r\n")) {
            throw new \Exception("send commit off ERR");
        }
        $result = fgets($this->resource, 1024);
        if ($result != "auto commit off\r\n") {
            throw new \Exception("set commit off ERR : $result");
        }

        $this->commitState = OpoolCommitStateEnum::COMMIT_OFF;
        return $result;
    }

    /**
     * @param $bindname
     * @param $value
     */
    public function bind($bindname, $value)
    {
        $this->bindinfo .= "--BIND-- $bindname " . strlen($value) . "\r\n" . $value . "\r\n";
    }

    /**
     * @param $bindname
     * @param $value
     */
    public function bindN($bindname, $value)
    {
        $this->bindinfo .= "--BIND_N-- $bindname " . strlen($value) . "\r\n" . $value . "\r\n";
    }

    /**
     * @param $bindname
     * @param $data
     * @param $datalen
     */
    public function bindBlob($bindname, $data, $datalen)
    {
        $this->bindinfo .= "--BIND_BLOB-- $bindname " . $datalen . "\r\n" . $data . "\r\n";
    }

    /**
     * @param $str
     * @return string
     */
    public function parseAffectedRows($str)
    {
        if (strncmp("affectedRows\t", $str, 13)) {
            return trim($str);
        } else {
            return trim(substr($str, 13));
        }
    }

    /**
     * @return mixed
     */
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * @param $sql
     * @return string
     */
    public function sqlParse($sql)
    {
        $desc = ($this->description) ? ("/* " . $this->description . " */ ") : "";
        if (!$desc && !$this->bindinfo) {
            $desc .= "/*" . $_SERVER["SCRIPT_NAME"] . "*/ ";
        }

        $sql = $this->bindinfo . $desc . $sql . OpoolCommitStateEnum::SQL_INPUT;
        $this->bindinfo = $this->description = "";

        return $sql;
    }

    /**
     * @param $sql
     * @param $errMsg
     * @throws OpoolException
     */
    public function throwException($sql, $errMsg)
    {
        $err = "";
        $err .= "ERROR : [" . $this->getEnv() . "][ $sql ] => ";
        $err .= $errMsg;

        while ($result = fgets($this->resource, 1024)) {
            if (preg_match("/" . OpoolCommitStateEnum::SQL_ERR_END . "/", $result)) {
                break;
            }
            $err .= $result;
        }

        preg_match("/ORA-\\d*/", $err, $arr);

        $ora_code = '';

        if ($arr) {
            $ora_code = $arr[0];
        }

        throw new OpoolException("SQL Server Err2:" . $err, 0, $ora_code);
    }

    /**
     * @param $sql
     * @param int $timeouts
     * @throws OpoolException
     * @throws \Exception|OpoolException
     */
    public function query($sql, $timeouts = 0)
    {
        $timeouts = isset($timeouts) ? ($timeouts + 0) : OpoolCommitStateEnum::WAIT_TIME;
        if ($timeouts <= 0) {
            $timeouts = OpoolCommitStateEnum::WAIT_TIME;
        }

        if (!$sql) {
            OpoolException::throwSqlIsNull();
        }

        if ($this->fetching) {
            OpoolException::throwConnIsFetching();
        }

        socket_set_timeout($this->resource, $timeouts);
        if (!fputs($this->resource, $this->sqlParse($sql))) {
            OpoolException::throwSendSqlError();
        }

        $result = fgets($this->resource, 1024);

        if ($result == OpoolCommitStateEnum::SQL_ERR) {
            $result = fgets($this->resource, 1024);
            if ($this->backup && preg_match("/" . OpoolCommitStateEnum::SQL_SERVER_ERR . "/", $result)) {
                //todo If you need to use backup opool connection, please implement it when opool connection having problem.
                throw new \Exception("SQL Server Err1:" . $result);
            } else {
                $this->throwException($sql, $result);
            }
        } elseif (strncmp($result, OpoolCommitStateEnum::SQL_BEGIN, strlen(OpoolCommitStateEnum::SQL_BEGIN)) == 0) {
            //while doing select job
            socket_set_timeout($this->resource, $timeouts);
            unset($this->colname);
            $result = substr($result, strlen(OpoolCommitStateEnum::SQL_BEGIN), -2);
            $this->colname = explode(",", strtolower($result));
            $this->colnums = count($this->colname);
            $this->sql = $sql;
            $this->fetching = true;
        } elseif ($result == OpoolCommitStateEnum::SQL_EDIT_OK) {
            //while doing execute job
            socket_set_timeout($this->resource, $timeouts);
            $this->sql = $sql;
            $this->affectedRows = $this->parseAffectedRows(fgets($this->resource, 1024));
        } elseif ($result == "") {  //     //回傳空白 以下為修改 20040804 for grover
            $sk_info = stream_get_meta_data($this->resource);
            if ($sk_info["timed_out"] == 1) {  // 表示執行過久 time out , 回應前端 time out
                OpoolException::throwQueryTimeout($timeouts, $sql);
            }

            $this->isBroken = true;
            $this->fetching = true; //把壞掉的 當做一直有在用
            throw new \Exception("Query result is empty [ " . $sql . " ]");
        } else {
            throw new \Exception($result);
        }
    }

    /**
     * using select count(*) to get number of rows, now it will always return -1
     * @return int
     * @internal param $obj
     * @deprecated
     */
    public function numRows()
    {
        return -1;
    }

    public function nextRow($timeouts = "0")
    {
        return $this->fetchRow($timeouts);
    }

    /**
     * query before fetch, return array if has value. reutrn null while query is end
     *
     * @param bool $index_by_name
     * @param int $timeouts
     * @return array|bool
     * @throws \Exception
     */
    public function fetchRow($index_by_name = false, $timeouts = 0)
    {
        if (!$this->fetching) {
            return false;
        }

        if ($this->isBroken) {
            throw new \Exception("this connection is broken");
        }

        $timeouts = isset($timeouts) ? ($timeouts + 0) : $timeouts = OpoolCommitStateEnum::WAIT_TIME;

        if ($timeouts <= 0) {
            $timeouts = OpoolCommitStateEnum::WAIT_TIME;
        }
        socket_set_timeout($this->resource, $timeouts);
        $row_value = "";

        while ($result = fgets($this->resource, OpoolCommitStateEnum::ROW_BUFFER)) {

            if ($result == OpoolCommitStateEnum::SQL_END) {
                $this->fetching = false;
                return false;
            }

            if ($result == OpoolCommitStateEnum::SQL_ROW) {
                break;
            }

            $row_value .= $result;
        }

        $row = explode(OpoolCommitStateEnum::SQL_COL, substr($row_value, 0, -2));

        if ($index_by_name) {
            $nameRow = [];
            foreach ($this->colname as $idx => $colname) {
                $nameRow[$colname] = &$row[$idx];
            }
            return $nameRow;
        } else {
            return $row;
        }
    }

    /**
     * using fetchRow(true) instead.
     * column indexed by number not exists any more
     * @return null
     * @deprecated
     */
    public function fetchArray()
    {
        return null;
    }

    /**
     *
     */
    public function freeResult()
    {
        if (!isset($this->id) || !$this->resource || !$this->fetching) {
            return;
        }

        while ($result = fgets($this->resource, OpoolCommitStateEnum::ROW_BUFFER)) {
            if ($result == OpoolCommitStateEnum::SQL_END) {
                break;
            }
        }

        $this->fetching = false;
        socket_set_timeout($this->resource, OpoolCommitStateEnum::WAIT_TIME);
        return;
    }

    /**
     *
     */
    public function close()
    {
        if ($this->resource) {
            @fclose($this->resource);
            unset($this->resource);
        }

        /**
         * @TODO add close all connection in manager
         */
    }

    /**
     * @param $sql
     * @param int $start
     * @param int $count
     * @param int $timeouts
     * @return array|null
     * @throws \Exception|OpoolException
     */
    public function queryAll($sql, $start = 0, $count = null, $timeouts = 0)
    {
        $this->query($sql, $timeouts);

        $index = 0;

        $result = [];

        while ($row = $this->fetchRow(true)) {
            if ($count && ($index >= $start + $count)) {
                break;
            }
            if ($index >= $start) {
                $result[] = $row;
            }
            $index++;
        }

        $this->freeResult();

        if (!empty($result) && is_array($result)) {
            return $result;
        } else {
            return [];
        }
    }

    /**
     * @param $sql
     * @param string $timeouts
     * @return array|bool
     * @throws \Exception
     */
    public function queryFirst($sql, $timeouts = "0")
    {
        $this->query($sql, $timeouts);
        $result = $this->fetchRow(true);

        $this->freeResult();

        return $result;
    }
}
