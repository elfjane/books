<?php
namespace Lib\Tools;
use Redis;

class RedisTool
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST, 6379);
    }

    public function set($name, $value)
    {
        $this->redis->set($name, $value);
    }

    public function get($name)
    {
        $data = $this->redis->get($name);
        return $data;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function setToken($key, $value)
    {
        $name = REDIS_HOST_KEY_TOKEN . $key;
        $this->set($name, $value);
    }

    public function getToken($key)
    {
        $name = REDIS_HOST_KEY_TOKEN . $key;
        $data = $this->get($name);
        return $data;
    }
}
