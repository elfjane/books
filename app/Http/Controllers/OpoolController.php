<?php
/*
    2025/8/21 elfjane test
*/
namespace App\Http\Controllers;
use App\Models\Opool\PayOrder;
use App\Models\Opool\ZelfjaneTest;
use App\Models\User;

class OpoolController extends Controller
{
    protected $payOrder;
    protected $zelfjaneTest;
    protected $user;
    public function __construct(PayOrder $payOrder, ZelfjaneTest $zelfjaneTest, User $user)
    {
        $this->payOrder = $payOrder;
        $this->zelfjaneTest = $zelfjaneTest;
        $this->user = $user;
    }
    public function __default()
    {

        ppLog('test');
        ppLog('test222');
        ppError('test2221');
        $this->user->insert(["time" => 123]);
        //throw new Exception("檔案讀取失敗");

        $request     = array('uid');
        $this->setRequestData($request);
        $uid = $this->requestData['uid'];

        $insertData = [
            'ID' => 1234555,
            'COLUMN1' => 12345556,
            'COLUMN2' => '2025-01-02'
        ];
        $dataElfjane['insert'] = $this->zelfjaneTest->insert($insertData);
        //$results['delete'] = $this->zelfjaneTest->delete();
        $dataElfjane['delete'] = $this->zelfjaneTest->where('ID', 1234555)->delete();
        $dataElfjane['find'] = $this->zelfjaneTest->find(1);
        $dataElfjane['find_key'] = $this->zelfjaneTest->find('2','ID');
        $dataElfjane['select_where_get'] = $this->zelfjaneTest->where('COLUMN1', 'test3')->get();
        $dataPayOrder['select_where_first'] = $this->payOrder->select('ORDER_ID', 'payee')->where('ORDER_ID', '=','A18051263375301')->first();
        $results = [
            'elfjane_test' => $dataElfjane,
            'pay_order' => $dataPayOrder,
        ];

        $this->set('elfjane_test', $dataElfjane);
        $this->set('pay_order', $dataPayOrder);
        $this->set("uid",       $uid);
        $this->set("aaa",       env('A'));
        $this->set("aaac",       config('database.connections.opool.host'));
        $this->setAccept();
        ppLog("kye",$this->displayData);
        ppDebug('im debug');
    }
}
