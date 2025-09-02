<?php

namespace App\Models\Opool;

use Exception;
use Lib\Database\OpoolDriver\OpoolOrm;


class PayOrder extends OpoolOrm
{
    protected $table = 'PAY_ORDER';

    /**
     * @param $orderId
     * @param string $field
     * @return array|bool
     * @throws Exception
     */
    public function findByOrderId($orderId, $field = "*")
    {
        $sql = "SELECT {$field} FROM PAY_ORDER WHERE ORDER_ID = :order_id";
        $binds = [
            'order_id' => $orderId
        ];

        $result = $this->dbAdpt->selectFirst($sql, $binds);

        return $result;
    }
}
