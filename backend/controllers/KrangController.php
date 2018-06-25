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
use yii\web\Response;

class KrangController extends Controller
{
    private $api_key = "";
    private $secret_key = "";
    private $contract_type = "";
    private $symbol = "";
    private $coin_type = "";
    private $max_queue = "";

    function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->api_key = Yii::$app->params['api_key'];
        $this->secret_key = Yii::$app->params['secret_key'];
        $this->contract_type = Yii::$app->params['contract_type'];
        $this->symbol = Yii::$app->params['symbol'];
        $this->coin_type = Yii::$app->params['coin_type'];
        $this->max_queue = Yii::$app->params['max_queue'];
    }

    public $tlist = [];
    private function _make_queue($data)
    {
        //$data = ['当前时间', '当前价格', '减去上一次所得的值']
        $this->tlist[] = $data;
        if (count($this->tlist) > $this->max_queue){
            array_shift($this->tlist);
        }
    }

    public function actionTs()
    {
        $this->layout = false;
        return $this->render('ts');
    }	

    public function actionTest()
    {
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));
        $params = array('symbol' => $this->symbol, 'contract_type' => $this->contract_type);
        $cur_trade_info = $client -> tickerApi($params);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['code'=>($cur_trade_info->ticker->last??0).'pp','msg'=>$_POST['msg'], 'data'=>['bb']];
    }

    public function actionTest2()
    {
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));
        $params = array('symbol' => $this->symbol, 'contract_type' => $this->contract_type);
        $cur_trade_info = $client -> tickerApi($params);

        print_r($cur_trade_info);
    }

    public function actionIndex()
    {
        //OKCoin DEMO 入口
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));

//        $i = 50;
//        while ($i > 0){
//            $tmp = [];
//            usleep(500000);
//
//            //获取OKCoin行情（盘口数据）
//            $params = array('symbol' => $this->symbol, 'contract_type' => $this->contract_type);
//            $cur_trade_info = $client -> tickerApi($params);
//
//            $tmp['time'] = $cur_trade_info->date;
//            $tmp['cur_price'] = $cur_trade_info->ticker->last;
//            $tmp['d_value'] = end($this->tlist)['cur_price']?($cur_trade_info->ticker->last - end($this->tlist)['cur_price']):0;
//
//            $this->_make_queue($tmp);
//            $i--;
//        }
//
//        print_r($this->tlist);


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
        if ($result->info){
            $result = $result->info->ltc;
        }
        print_r($result);

        //逐仓用户持仓查询
        $params = array('api_key' => $this->api_key, 'symbol' => $this->symbol, 'contract_type' => 'this_week', 'type' => 1);
        $result = $client -> singleBondPositionFutureApi($params);
        print_r($result);
    }
}
