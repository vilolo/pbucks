<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/6/10
 * Time: 1:04
 */

namespace backend\controllers;

use backend\controllers\src\ok\OKCoin;
use Yii;
use yii\base\Controller;

class KrangController extends Controller
{
    private $api_key = "";
    private $secret_key = "";
    private $contract_type = "";
    private $symbol = "";
    private $coin_type = "";

    function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->api_key = Yii::$app->params['api_key'];
        $this->secret_key = Yii::$app->params['secret_key'];
        $this->contract_type = Yii::$app->params['contract_type'];
        $this->symbol = Yii::$app->params['symbol'];
        $this->coin_type = Yii::$app->params['coin_type'];
    }

    public function actionIndex()
    {
        //OKCoin DEMO 入口
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));

        //获取OKCoin行情（盘口数据）
        $params = array('symbol' => $this->symbol, 'contract_type' => $this->contract_type);
        $result = $client -> tickerApi($params);
        print_r($result);

        //获取用户信息
//        balance:账户余额
//        available:合约可用
//        balance:账户(合约)余额
//        bond:固定保证金
//        contract_id:合约ID
//        contract_type:合约类别
//        freeze:冻结
//        profit:已实现盈亏
//        unprofit:未实现盈亏
//        rights:账户权益
        $params = array('api_key' => $this->api_key);
        $result = $client -> fixUserinfoFutureApi($params);
        if ($result['info']){
            $result = $result['info'][$this->coin_type];
        }
        print_r($result);

        //逐仓用户持仓查询
        $params = array('api_key' => $this->api_key, 'symbol' => 'btc_usd', 'contract_type' => 'this_week', 'type' => 1);
        $result = $client -> singleBondPositionFutureApi($params);
        print_r($result);
    }
}