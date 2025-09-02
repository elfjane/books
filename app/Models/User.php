<?php
namespace App\Models;
use Lib\Database\Mysql\MysqlOrm;

class User extends MysqlOrm
{
    protected $table = 'users';
    public function checkUid($uid)
    {
        $user = static::where('uid', $uid)->first();

        return $user ? $user->uid : 0;
    }
}