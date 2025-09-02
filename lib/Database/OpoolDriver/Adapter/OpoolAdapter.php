<?php

namespace Lib\Database\OpoolDriver\Adapter;

use Lib\Database\OpoolDriver\Exception\OpoolException;
use Lib\Database\OpoolDriver\OPoolConnection;
use Lib\Database\OpoolDriver\OpoolConnFactory;
use Lib\Database\OpoolDriver\OPoolFunc\FuncBase;
use Lib\Entities\EntityBase;
use RuntimeException;

/**
 * Class OpoolAdapter
 * @package Lib\Database\OpoolDriver\OpoolAdapter
 */
class OpoolAdapter implements OpoolAdapterInterface
{
    /** @var OPoolAdapter $instance */
    private static $instance;
    private $factory;

    /**
     * OpoolAdapter constructor.
     * @param $envStr
     * @param string $errorDo
     */
    public function __construct($envStr, $errorDo = "")
    {
        $this->factory = new OpoolConnFactory($envStr, $errorDo);
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->closeFactory();
    }

    /**
     * @throws \Exception
     */
    public static function cleanAll()
    {
        if (OPoolAdapter::$instance !== null) {
            OPoolAdapter::$instance->closeFactory();
        }
    }

    /**
     * @throws \Exception
     */
    public function closeFactory()
    {
        if ($this->factory !== null) {
            $this->factory->closeAll();
        }

        $this->factory = null;
    }

    /**
     * @param string $opoolIp
     * @return mixed | OpoolAdapter
     */
    public static function getInstance($opoolIp = null)
    {
        if (!isset(OpoolAdapter::$instance)) {

            if ($opoolIp === null) {
                $opoolIp = config('database.connections.opool.host') . ':' . config('database.connections.opool.port');
            }

            OpoolAdapter::$instance = new OpoolAdapter($opoolIp, "");
        }

        return OpoolAdapter::$instance;
    }

    /**
     * @param OpoolAdapter $adapter
     */
    public static function setInstance(OpoolAdapter $adapter)
    {
        OpoolAdapter::$instance = $adapter;
    }

    public static function getERPInstance()
    {
        $opoolIp = config('database.connections.opool_long.host') . ':' . config('database.connections.opool_long.port');

        return static::getInstance($opoolIp);
    }

    /**
     * @param $sql
     * @param array $binds
     * @param int $timeout
     * @param int $page
     * @param int $rowsPerPage
     * @return array|null
     * @throws \Exception
     */
    public function select($sql, array $binds, $timeout = 0, $page = 0, $rowsPerPage = 10)
    {
        $connection = $this->factory->getOpoolConn();

        try {

            $this->bindParams($binds, $connection);

            //12c only
            if ($page > 0) {
                $rp = intval($rowsPerPage);
                $offset = (intval($page) - 1) * $rp;

                $sql = $sql . " OFFSET {$offset} ROWS FETCH NEXT {$rp} ROWS ONLY";
            }

            $sql = $this->buildFuncSql($sql, $binds);

            $result = $connection->queryAll($sql, $timeout);

            return $result;
        } catch (OPoolException $exception) {

            if (isset($connection) && $connection !== null) {
                $this->factory->close($connection);
            }

            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param array $binds
     * @param int $timeout
     * @return array|bool
     * @throws \Exception
     */
    public function selectFirst($sql, array $binds, $timeout = 0)
    {
        $connection = $this->factory->getOpoolConn();

        try {
            $this->bindParams( $binds, $connection);

            $sql = $this->buildFuncSql($sql, $binds);

            $result = $connection->queryFirst($sql);

            return $result;
        } catch (OPoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }

    }

    /**
     * @param $table
     * @param $binds
     * @param array $columns
     * @param int $timeout
     * @return array|null
     * @throws \Exception
     */
    public function selectTable($table, $binds, array $columns = [], $timeout = 0)
    {
        $sql = $this->buildSql($table, $binds, $columns, $bindData);

        return $this->select($sql, $bindData, $timeout);
    }


    /**
     * @param $table
     * @param $binds
     * @param array $columns
     * @param int $timeout
     * @return array|bool
     * @throws \Exception
     */
    public function selectTableFirst($table, $binds, array $columns = [], $timeout = 0)
    {
        $sql = $this->buildSql($table, $binds, $columns, $bindData);

        return $this->selectFirst($sql, $bindData, $timeout);
    }

    private function buildSql($table, $binds, array $columns = [], &$bindData = null)
    {
        $w = [];

        // build column
        $cols = implode(",", $columns);
        if (!$cols) {
            $cols = "*";
        }

        // build where sql
        if (is_object($binds)) {
            $b = get_object_vars($binds);
        } else {
            if (is_array($binds)) {
                $b = $binds;
            } else {
                throw new RuntimeException("bind must be array or object");
            }
        }

        foreach ($b as $idx => $val) {
            if (!is_null($val)) {
                $w[] = "{$idx} = :{$idx}";
                $bindData[$idx] = $val;
            }
        }

        if (!$w) {
            throw new RuntimeException("can't query full table, where condition must be assigned");
        }

        $where = implode(" and ", $w);

        $sql = "select {$cols} from {$table} where {$where}";

        return $sql;
    }

    /**
     * @param string $tableName
     * @param array $data
     * @param int $timeout
     * @param string $tableSchema
     * @return mixed
     * @throws \Exception
     */
    public function insert($tableName, array $data, $timeout = 0, $tableSchema = null)
    {
        $connection = $this->factory->getOpoolConn();

        try {
            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ":{$col}", $columns);
            if ($tableSchema !== null) {
                $sql = "INSERT INTO {$tableSchema}.{$tableName}";
            } else {
                $sql = "INSERT INTO {$tableName}";
            }
            $sql .= " (" . implode(', ', $columns) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            // $binds = $entity->toArray();

            // $bounds = [];
            // foreach ($binds as $fieldName => $value) {
            //     $bounds[$fieldName] = $this->convertCondition($fieldName, $value);
            // }

            // $fieldNames = array_keys($data);
            // $fields = '( ' . implode(' ,', $fieldNames) . ' )';



            // $bound = '(' . implode(', ', $bounds) . ')';
            // $sql .= $fields . ' VALUES ' . $bound;

            $this->bindParams($data, $connection);

            $connection->query($sql, $timeout);

            $affectedRows = $connection->getAffectedRows();

            $connection->freeResult();
            return $affectedRows;
        } catch (OPoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param EntityBase $entity
     * @param int $timeout
     * @param string $tableSchema
     * @param array|null $checkFields
     * @return mixed
     * @throws \Exception
     */
    public function update(EntityBase $entity, $timeout = 0, $tableSchema = null, ?Array $checkFields = null)
    {
        $connection = $this->factory->getOpoolConn();

        try {

            $tableName = $entity->getTableName();
            $binds = $entity->getDeltaArray();
            $pks = $entity->getPKValues();

            if ($pks === null || empty($pks)) {
                throw new RuntimeException("pk must assigned");
            }

            if ($tableSchema !== null) {
                $sql = "UPDATE {$tableSchema}.{$tableName} SET\n";
            } else {
                $sql = "UPDATE {$tableName} SET\n";
            }

            $setCond = '';
            foreach ($binds as $fieldName => $value) {
                $condition = $this->convertCondition($fieldName, $value);
                $setCond .= "{$fieldName} = {$condition}, ";
            }
            $setCond = rtrim($setCond, ', ');

            $cond = [];
            foreach ($pks as $field => $value) {
                $cond[] = "{$field} = :pk_{$field}";
                $binds["pk_{$field}"] = $value;
            }

            $whereCond = '';
            if (!empty($cond)) {
                $whereCond = implode(" and ", $cond);
            }

            $checkCond = "";
            if ($checkFields) {
                $checkCondAry = [];
                foreach ($checkFields as $fieldName => $value) {
                    $checkCondAry[] = "{$fieldName} = :ori_{$fieldName}";
                    $binds["ori_{$fieldName}"] = $value;
                }

                $checkCond = "AND " . implode(" AND ", $checkCondAry);
            }

            if ($setCond !== '') {
                $sql = "{$sql} {$setCond} WHERE {$whereCond} {$checkCond}";

                $this->bindParams($binds, $connection);
                $connection->query($sql, $timeout);
                $affectedRows = $connection->getAffectedRows();
            } else {
                $affectedRows = 0;
            }

            $connection->freeResult();

            return $affectedRows;
        } catch (OPoolException $exception) {
            $this->factory->close($connection);

            throw $exception;
        }
    }

    /**
     * @param $table
     * @param $values
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function insertBatch($table, $values, $timeout = 0)
    {
        try {
            $connection = $this->factory->getOpoolConn();

            $sql = "";

            $fieldNames = array_keys($values[0]);
            $fields = '( ' . implode(', ', $fieldNames) . ' )';

            $binds = [];
            foreach ($values as $index => $data) {
                $conds = [];
                foreach ($data as $fieldName => $value) {
                    $binds["{$fieldName}{$index}"] = $data[$fieldName];
                    $conds["{$fieldName}{$index}"] = $this->convertCondition($fieldName, $value, $index);
                }

                $bound = '(' . implode(', ', $conds) . ')';
                $sql .= "INTO {$table} {$fields} VALUES {$bound} \n";
            }

            $sql = "INSERT ALL \n {$sql} SELECT * FROM dual";

            ppDebug($sql);
            ppDebug($binds);

            $this->bindParams($binds, $connection);
            $connection->query($sql, $timeout);
            $affectedRows = $connection->getAffectedRows();

            $connection->freeResult();
            return $affectedRows;
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param array $binds
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function execute($sql, array $binds, $timeout = 0)
    {
        $connection = $this->factory->getOpoolConn();

        try {

            $this->bindParams($binds, $connection);
            $sql = $this->buildFuncSql($sql, $binds);

            $connection->query($sql, $timeout);
            $affectedRows = $connection->getAffectedRows();

            $connection->freeResult();

            return $affectedRows;

        } catch (OPoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function nativeExecute($sql, $timeout = 0)
    {
        try {
            $connection = $this->factory->getOpoolConn();
            $connection->query($sql, $timeout);

            $affectedRows = $connection->getAffectedRows();
            $connection->freeResult();

            ppDebug($sql);

            return $affectedRows;

        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param int $timeout
     * @return array|null
     * @throws \Exception
     */
    public function nativeSelect($sql, $timeout = 0)
    {
        try {
            $connection = $this->factory->getOpoolConn();

            $result = $connection->queryAll($sql);

            ppDebug($sql);
            ppDebug($result);

            return $result;
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param int $timeout
     * @return array|bool
     * @throws \Exception
     */
    public function nativeSelectFirst($sql, $timeout = 0)
    {
        try {
            $connection = $this->factory->getOpoolConn();

            $result = $connection->queryFirst($sql);

            ppDebug($sql);
            ppDebug($result);

            return $result;
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param $sql
     * @param array $binds
     * @param $timeout
     * @param $closure
     * @throws \Exception
     */
    public function each($sql, array $binds, $timeout, $closure)
    {
        try {
            ppDebug($sql);
            ppDebug($binds);

            $connection = $this->factory->getOpoolConn();

            //bind array
            if ($binds && is_array($binds)) {
                foreach ($binds as $key => $value) {
                    $this->bindParams($binds, $connection);
                }
            }

            $connection->query($sql, $timeout);
            $index = 0;
            while ($row = $connection->fetchRow(true)) {
                $closure($index, $row);
                $index++;
            }

            $connection->freeResult();
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    public function startTransaction()
    {
        try {
            $connection = $this->factory->getOpoolConn();
            $connection->setCommitOff();
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    public function commit()
    {
        try {
            $connection = $this->factory->getOpoolConn();
            $connection->commit();
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    public function rollback()
    {
        try {
            $connection = $this->factory->getOpoolConn();
            $connection->rollback();
        } catch (OpoolException $exception) {
            $this->factory->close($connection);
            throw $exception;
        }
    }

    /**
     * @param array $binds
     * @param OPoolConnection $conn
     */
    private function bindParams(array $binds, &$conn)
    {
        if ($binds && $binds !== '' && is_array($binds)) {
            foreach ($binds as $key => $value) {
                if (!($value instanceof FuncBase)) {
                    $conn->bind($key, $value);
                }
            }
        }
    }

    /**
     * @param $fieldName
     * @param $value
     * @param string $index
     * @return string
     */
    private function convertCondition($fieldName, $value, $index = '')
    {
        if ($value instanceof FuncBase) {
            $condition = $value->getSql();
        } else if (preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $value)) {
            $condition = "to_date(:{$fieldName}{$index}, 'yyyy/mm/dd')";
        } elseif (preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value)) {
            $condition = "to_date(:{$fieldName}{$index}, 'yyyy/mm/dd hh24:mi:ss')";
        } elseif (preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}.[0-9]{2}.[0-9]{2}.[0-9]{3}$/', $value)) {
            $condition = "to_timestamp(:{$fieldName}{$index}, 'YYYY/MM/DD HH24.MI.SS.FF')";
        } elseif (preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}.[0-9]{3}$/', $value)) {
            $condition = "to_timestamp(:{$fieldName}{$index}, 'YYYY/MM/DD HH24:MI:SS.FF')";
        } else {
            $condition = ":{$fieldName}{$index}";
        }

        return $condition;
    }

    public function buildFuncSql($sql, array $binds)
    {
        //replace with func base values
        foreach ($binds as $idx => $func) {
            if ($func instanceof FuncBase) {
                $sql = str_replace(":" . $idx, $func->getSql(), $sql);
            }
        }
        return $sql;
    }
}
