<?php
namespace Lib\Database\Mysql;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Exception;

abstract class MysqlOrm extends Model
{
    public $_debug_mode = CRON_DEBUG_DB_MODE;
    protected $table = null;
    public $timestamps = true; // 若資料表無 created_at/updated_at 可停用


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (empty($this->table)) {
            $this->table = strtolower(get_class($this));
        }
    }

    public function execSQL($sql)
    {
        return Capsule::connection()->statement($sql);
    }


    public function execQuery($sql)
    {
        $ret = Capsule::connection()->select($sql);
        return $ret;
    }

    public function saveObject(array $insert_data, bool $error_check = false)
    {
        foreach ($insert_data as $key => $val) {
            $this->$key = $val;

        }
        $result = $this->save();

        return $result;
    }

    public function insertObject(array $insert_data, bool $error_check = false)
    {
        $data = [];
        foreach ($insert_data as $row) {
            $data[$row] = $this->$row;
        }
        $result = static::insert($data); // 執行 INSERT

        return $result;
    }

    public function getCountAdd($column)
    {
        return Capsule::raw("{$column} + 1");
    }

    public function getTimetampDiff($column = 'created_at')
    {
        return Capsule::raw("TIMESTAMPDIFF(SECOND, {$column}, NOW())");
    }


    public function getTimetampDiffColumn($startTime, $endTime)
    {
        return Capsule::raw("TIMESTAMPDIFF(SECOND, {$endTime}, {($startTime})");
    }

    public function getNow()
    {
        return Capsule::raw('NOW()');
    }

    public function selectById($id)
    {
        return static::find($id);
    }

    public function insertWithReturnId(array $insertData, bool $error_check = false)
    {
        // 單筆資料插入
        $record = static::create($insertData);

        // 回傳 insert 後的 id
        return $record->id ?? null;
    }

    public function insertArray($insert_data, $error_check = true)
    {
        $result = static::insert($insert_data);

        return $result;
    }

    public function updateObject($updateData, $checkId, $error_check = true)
    {
        if (empty($checkId)) {
            throw new Exception('system error update data error', 1005);
        }

        return $this->where($checkId, $this->$checkId)->update($updateData);
    }

    public function selectAll()
    {
        $ret = $this->all();
        return $ret;
    }
    public function countObject($where)
    {
        if (!isset($where) || !is_array($where)) {
            throw new Exception('system error insert data error', 1004);
        }
        return $this->where($where)->count();

    }
    public function get_last_id()
    {
        $result = Capsule::connection()->select('SELECT LAST_INSERT_ID() AS id');
        $id = $result[0]->id;
        return $id;
    }

    public function callSP($sp_name, $sp_data)
    {
        $sql_row = implode(', ', array_map(function($row) {
            return "'" . addslashes($row) . "'";
        }, $sp_data));

        $sql = "CALL {$sp_name}({$sql_row})";
        if ($this->_debug_mode) {
            $logdata = ['sql' => $sql];
            wLog('sql', $sql);
            file_put_contents('logs/mysql_debug_' . date(CRON_BASE_LOG_DATE) . '.txt', $logdata, FILE_APPEND);
        }
        $ret = $this->execSQL($sql);
        if (!$ret) {
            throw new Exception('system error call Stored Procs Error', 1001);
        }
        return $ret;

    }
    public function callReturnSP($spName, $sp_in_data, $sp_out_data)
    {
        $spInput = implode(', ', array_map(function($row) {
            return "'" . addslashes($row) . "'";
        }, $sp_in_data));

        $sqlReturn = implode(', ', array_map(function($row) {
            return "@" . addslashes($row);
        }, $sp_out_data));

        if (empty($sqlReturn)) {
            $sql = "CALL {$spName}({$spInput})";
        } else {
            $sql = "CALL {$spName}({$spInput}, {$sqlReturn})";
        }

        if ($this->_debug_mode) {
            //$logdata = "[" . date(CRON_BASE_DATE) . "] " . $sql . PHP_EOL;
            //file_put_contents('logs/mysql_debug_' . date(CRON_BASE_LOG_DATE) . '.txt', $logdata, FILE_APPEND);
            wLog('mysql_debug',['sql'=> $sql]);
        }
        $this->execSQL($sql);

        // 取得回傳資料
        $sql_select = implode(', ', array_map(function($row) {
            return "@" . $row . ' AS ' . $row;
        }, $sp_out_data));

        $sql = "SELECT " . $sql_select;

        if ($this->_debug_mode) {
            //$logdata = "[" . date(CRON_BASE_DATE) . "] " . $sql . PHP_EOL;
            //file_put_contents('logs/mysql_debug_' . date(CRON_BASE_LOG_DATE) . '.txt', $logdata, FILE_APPEND);
            wLog('mysql_debug',['sql'=> $sql]);
        }
        $ret = $this->execQuery($sql);

        if (count($ret) != 1) {
            throw new Exception('system error call Stored Procs Error', 1007);
        }
        return (array) $ret[0];

    }
}