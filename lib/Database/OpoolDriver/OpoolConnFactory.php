<?php
/**
 * Author: elfjane
 * Date Time: 2025/8/12 17:08
 */

namespace Lib\Database\OpoolDriver;

use Lib\Database\OpoolDriver\Enums\OpoolCommitStateEnum;
use Lib\Database\OpoolDriver\Exception\OpoolException;

class OpoolConnFactory
{
    public $sql;
    public $conn;
    public $err;
    public $result;
    public $poolIp;
    public $poolPort;

    /*
      Err [7] OCI_ERROR: ORA-03113: end-of-file on communication channel
      Err [3] OCI_ERROR: ORA-12541: TNS:no listener
      Err [7] OCI_ERROR: ORA-03114: not connected to ORACLE
      Err [17] OCI_ERROR: ORA-01034: ORACLE not available
      ORA-27101: shared memory realm does not exist
      SVR4 Error: 2: No such file or directory
     */

    public $rowBuffer = 1024000;
    public $switch = true; // 切換pool
    public $backup = false; // 啟動backuppool
    public $retryLimit = 5;
    public $bindinfo = "";
    public $i = 0;
    public $spoolList = "_oracle_pool";
    public $eKey; // default pool group , opool ip seperate by ","
    public $backupenv = ''; // backup pool group
    public $errLog = true;
    public $commitOffConn = 0;
    public $description;
    public $errdo = 'pool_errlog';
    public $mEnv = 0; // 0 last cache, 1 random
    public $shm;
    public $shmVar;
    public $resetEnv = 0;

    private $errordo;
    private $connList = [];  //目前可用的連線
    private $envStr;
    private $envSettingList = [];

    public function __construct($envStr = null, $errordo = "")
    {
        if ($envStr === null) {
            throw new \InvalidArgumentException('should set db ip and port');
        }

        $this->setEnv($envStr, $errordo);
    }

    public function getConnList()
    {
        return $this->connList;
    }

    public function getEnvSettingList()
    {
        return $this->envSettingList;
    }

    public function getEnvSettingCount()
    {
        return count($this->envSettingList);
    }

    public function getConnCount()
    {
        return count($this->connList);
    }

    /**
     * using old connection if available, else create a new one
     */
    public function getOpoolConn()
    {
        /**
         * @var $conn OPoolConnection
         */
        foreach ($this->connList as $idx => $conn) {
            if (!$conn->fetching && !$conn->inTransaction) {
                return $conn;
            }
        }

        return $this->connect();
    }

    /**
     * @param $timeout
     * @return OPoolConnection
     * @throws \Exception
     */
    public function connect($timeout = OpoolCommitStateEnum::WAIT_TIME)
    {
        $conn = new OpoolConnection();
        $connTrys = 0;

        while (!$conn->resource && ($connTrys++ < $this->retryLimit)) {

            $connidx = rand(0, 100) % count($this->envSettingList);
            $opoolSetting = $this->envSettingList[$connidx];
            $conn->resource = fsockopen($opoolSetting["ip"], $opoolSetting["port"], $errno, $errstr, 1);

            if ($conn->resource) {  //if the socket is avalible, save it to pool

                socket_set_timeout($conn->resource, $timeout);
                $conn->id = count($this->connList);
                $conn->ip = $opoolSetting["ip"];
                $conn->port = $opoolSetting["port"];

                $this->connList[$conn->id] = &$conn;
            }

            if (method_exists($this, "err_log")) {
                $this->err_log($conn->getEnv() . "=>[ Error code: Maybe Connection closed by foreign host. ]");
            }

            usleep(300000);
        }

        //using backup plan, connect to another opool server
        if (!$conn->resource && $this->backup && $this->backupenv) {
            $this->setEnv($this->backupenv, "");
            $this->backup = false;
            return $this->connect();
        }

        //event the backup server is down, throw exception
        if ($connTrys >= $this->retryLimit) {
            OpoolException::throwConnectionError();
        }

        $conn->setCommitOn();
        return $conn;
    }

    /**
     * @param OPoolConnection $connection
     */
    public function close(OPoolConnection &$connection)
    {
        $connection->close();
        unset($this->connList[$connection->id]);
    }

    public function closeAll()
    {
        foreach ($this->connList as $idx => $conn) {
            $this->close($conn);
        }
    }

    /**
     * @param $envStr
     * @param $errordo
     */
    private function setEnv($envStr, $errordo)
    {
        $this->envStr = trim($envStr);
        $this->errordo = $errordo;

        if (!preg_match_all('/([^:\s]+)\s*:\s*(\d+)/s', $envStr, $arr)) {
            throw new \InvalidArgumentException("setting string syntax error:.$envStr");
        }

        //set available opool
        for ($i = 0; $i < count($arr[0]); $i++) {
            $this->envSettingList[] = ["ip" => $arr[1][$i], "port" => $arr[2][$i]];
        }
    }
}
