<?php

namespace Lib\Entities;

use Lib\Utils\StrUtil;

abstract class EntityBase
{
    private $oriAry = [];
    private $reflect;

    /**
     * @abstract
     * @return array[string]
     */
    abstract public function getPKFields();

    /**
     * EntityBase constructor.
     * @param $array
     */
    public function __construct($array = null)
    {
        if (is_object($array)) {
            $arr = get_object_vars($array);
            $this->fromArray($arr);
        } elseif ($array !== null) {
            $this->fromArray($array);
        }
    }

    public function getTableName()
    {
        $namespace = explode('\\', get_class($this));
        $tableName = $namespace[count($namespace) - 1];
        $tableName = StrUtil::convertCamelToUpper($tableName);

        return $tableName;
    }

    /**
     * @param array $arr
     */
    public function fromArray(array $arr)
    {
        if (!is_array($arr)) {
            throw new \RuntimeException();
        }

        foreach ($arr as $idx => $val) {
            if (property_exists($this, $idx)) {
                $this->$idx = $val;
                $this->oriAry[$idx] = $val;
            } elseif (property_exists($this, strtolower($idx))) {
                $iidx = strtolower($idx);
                $this->$iidx = $val;
                $this->oriAry[$iidx] = $val;
            } elseif (property_exists($this, strtoupper($idx))) {
                $iidx = strtoupper($idx);
                $this->$iidx = $val;
                $this->oriAry[$iidx] = $val;
            }
        }
    }

    public function toArray()
    {
        $result = array();

        if ($this->reflect === null) {
            $this->reflect = new \ReflectionClass($this);
        }

        $class_vars = $this->reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($class_vars as $prop) {

            $name = $prop->getName();
            $val = $this->$name;

            if ($val !== null) {
                $result[$name] = $this->$name;
            }
        }

        return $result;
    }

    public function getDeltaArray()
    {
        $arr = $this->toArray();

        $result = [];

        foreach ($arr as $name => $val) {
            if (!array_key_exists($name, $this->oriAry) || $arr[$name] !== $this->oriAry[$name]) {
                $result[$name] = $val;
            }
        }

        return $result;
    }

    public function clearDeltaArray(){
        $arr = $this->toArray();

        foreach ($arr as $idx => $val) {
            $this->oriAry[$idx] = $val;
        }
    }

    public function getPKValues()
    {
        $pk = $this->getPKFields();

        if ($pk === null || empty($pk)) {
            throw new \RuntimeException("Primary key must be defined");
        }

        foreach ($pk as $field) {
            if (isset($this->{$field}) && $this->{$field} !== null) {
                $result[$field] = $this->{$field};
            }
        }

        if (empty($result)) {
            throw new \RuntimeException("Primary key must be assigned in entity");
        }

        return $result;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set ($name, $value)
    {

    }
}
