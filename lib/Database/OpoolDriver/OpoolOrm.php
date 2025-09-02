<?php

namespace Lib\Database\OpoolDriver;

abstract class OpoolOrm extends OpoolOrmDao
{
    protected $where = [];
    protected $order = [];
    protected $columns = ['*']; // 預設全部欄位

    private function clearQuery() {
        $this->where = [];
        $this->order = [];
        $this->columns = ['*'];

    }
    public function select(string ...$columns): self
    {
        if (!empty($columns)) {
            $this->columns = $columns;
        }
        return $this;
    }

    public function where($column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = [$column, $operator, $value];
        return $this;
    }

    public function orderBy($column, $direction = 'asc'): self
    {
        $this->order[] = [$column, strtoupper($direction)];
        return $this;
    }

    public function first(): ?array
    {
        $cols = implode(', ', $this->columns);
        $sql = "SELECT {$cols} FROM {$this->getTable()}";
        $binds = [];
        if ($this->where) {
            $conditions = [];
            foreach ($this->where as [$col, $op, $val]) {
                $val = addslashes($val);
                $conditions[] = "{$col} {$op} :{$col}";
                $binds[$col] = $val;
            }
            $sql .= " WHERE " . implode( " AND ", $conditions);
        }

        if ($this->order) {
            $orders = [];
            foreach ($this->order as [$col, $dir]) {
                $orders[] = "{$col} {$dir}";
            }
            $sql .= " ORDER BY " . implode(", ", $orders);
        }
        $sql .= ' FETCH FIRST 1 ROW ONLY';
        $this->clearQuery();

        return $this->dbAdpt->selectFirst($sql, $binds);
    }

    public function find($id, string $primaryKey = 'id'): ?array
    {
        return $this->where($primaryKey, '=', $id)->first();
    }

    public function get(): array
    {
        $cols = implode(', ', $this->columns);
        $sql = "SELECT {$cols} FROM {$this->getTable()}";
        $binds = [];

        // WHERE
        if ($this->where) {
            $conditions = [];
            foreach ($this->where as [$col, $op, $val]) {
                $conditions[] = "{$col} {$op} :{$col}";
                $binds[$col] = $val;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // ORDER BY
        if ($this->order) {
            $orders = [];
            foreach ($this->order as [$col, $dir]) {
                $orders[] = "{$col} {$dir}";
            }
            $sql .= " ORDER BY " . implode(", ", $orders);
        }
        $this->clearQuery();

        return $this->dbAdpt->select($sql, $binds);
    }

    public function insert(array $data): bool
    {
        $binds = $data;
        $this->clearQuery();

        return $this->dbAdpt->insert($this->getTable(), $binds);
    }

    public function delete(): int
    {
        if (empty($this->where)) {
            throw new \RuntimeException("刪除資料前必須設定條件（WHERE），避免全表刪除）");
        }

        $sql = "DELETE FROM {$this->getTable()}";
        $binds = [];

        // WHERE 條件
        $conditions = [];
        foreach ($this->where as [$col, $op, $val]) {
            $conditions[] = "{$col} {$op} :{$col}";
            $binds[$col] = $val;
        }
        $sql .= " WHERE " . implode(" AND ", $conditions);
        $this->clearQuery();

        return $this->dbAdpt->execute($sql, $binds);
    }
}