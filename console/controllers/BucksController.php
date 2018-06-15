<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/6/10
 * Time: 11:56
 */

namespace console\controllers;


use backend\controllers\src\ok\OKCoin;
use PHPUnit\Framework\Exception;
use Yii;
use yii\base\Controller;
use yii\db\Query;

class BucksController extends Controller
{
    const BUY_TYPE = 1;
    const SEAL_TYPE = 2;

    const WIN_PERCENT = 8; //盈利10%平仓
    const LOST_PERCENT = -10;    //亏损20%出局

    const AVAILABLE_TRADE_PERCENT = 50;     //可用金额的交易比例，由于发出下单后未马上成交，不能控制合约张数

    const TRADE_OPEN_BUY = 1;
    const TRADE_OPEN_SEAL = 2;
    const TRADE_CLOSE_BUY = 3;
    const TRADE_CLOSE_SEAL = 4;

    private $api_key = "";
    private $secret_key = "";
    private $contract_type = "";
    private $symbol = "";
    private $coin_type = "";
    //private $max_amount = "";
    private $max_queue = "";

    private $tlist = [];
    private $is_start = false;  //每次启动使，先获取部分数据

    function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->api_key = Yii::$app->params['api_key'];
        $this->secret_key = Yii::$app->params['secret_key'];
        $this->contract_type = Yii::$app->params['contract_type'];
        $this->symbol = Yii::$app->params['symbol'];
        $this->coin_type = Yii::$app->params['coin_type'];
        //$this->max_amount = Yii::$app->params['max_amount'];
        $this->max_queue = Yii::$app->params['max_queue'];
    }

    public function actionIndex()
    {
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));

        //控制访问频率，0.5秒一次
        while (true){
            //print_r(time().'==');
            try{
                $data = [];
                usleep(500000);     //后期可以根据不同接口错开睡眠时间

                //效率考虑，可以固定id
                $sys_config = (new Query())->from("sys_config")->where(['type' => 1])->all();
                if ($sys_config){
                    //if (!$sys_config[0] || $sys_config[0]['name'] != 'is_run' || $sys_config[0]['value'] != 1){
                    if ($sys_config[0]['value'] != 1){
                        //var_dump("1&");
                        continue;
                    }
                }

                $account_info = $this->_account_info($client);

                //处理未完成订单
                $this->_handle_unfinish_order($client);

                //处理当前订单，返回当前市场行情
                $cur_trade_info = $this->_handle_order($client, $account_info);

                $data['time'] = $cur_trade_info->date;
                $data['cur_price'] = $cur_trade_info->ticker->last;
                $data['d_value'] = end($this->tlist)['cur_price']?($cur_trade_info->ticker->last - end($this->tlist)['cur_price']):0;

                //这个地方判断要加未完成的单
                //if ($sys_config[1]['value'] == 1 && (!$account_info || ($account_info->buy_amount+$account_info->sell_amount)<=$this->max_amount) ){
                if ($sys_config[1]['value'] == 1){
                    //判断是否需要下单
                    $params = array('api_key' => $this->api_key);
                    $result = $client -> fixUserinfoFutureApi($params);
                    if ($result->info){
                        $use_info = $result->info->ltc;
                        if ($use_info->contracts[0]->bond/$use_info->balance < 0.5){
                            $this->_create_order($client, $cur_trade_info);
                        }
                    }elseif(!$account_info || ($account_info->buy_amount+$account_info->sell_amount)<=$this->max_amount){
                        $this->_create_order($client, $cur_trade_info);
                    }
                }

                //保存数据到队列
                $this->_make_queue($data);
            }catch (Exception $e){
                //print_r($e->getMessage());
                file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：").$e->getMessage().PHP_EOL, 8);
            }catch (\OKCoin_Exception $oke){
                file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：").$oke->getMessage().PHP_EOL, 8);
            }
        }
    }

    private function _handle_unfinish_order(OKCoin $client)
    {
        //todo 待完善

        //判断是否有未完成的关闭单
            //有未关闭的判断是否取消重新下一次单
    }

    private function _handle_order(OKCoin $client, $account_info)
    {
        //方案一：试判断当前市场多空情况，智能平仓
            //快出（5%出，10%割）
            //慢出（10%出，20%割）
            //快出，慢出如何配合清仓重仓

        //方案二：
            //判断当前手上交易的合约是否亏损超过某个值（20%）
                //是超过就割肉
            //判断当前手上交易的合约是否盈利超过某个值（8%）
                //是则获利了结

        //方案三：根据保存的行情数据，判断是否单边，如果是是否需要加仓或其他止损

        //获取OKCoin行情（盘口数据）
        $params = array('symbol' => $this->symbol, 'contract_type' => $this->contract_type);
        $cur_trade_info = $client -> tickerApi($params);

        if ($account_info){
            $arr = $this->_handle_order_plain_a($account_info);

            if ($arr[self::BUY_TYPE][0] == 1 && $arr[self::BUY_TYPE][1] > 0){
                $params = array('api_key' => $this->api_key,
                    'symbol' => $this->symbol,
                    'contract_type' => $this->contract_type,
                    'type' => self::TRADE_CLOSE_BUY,
                    'price' => $cur_trade_info->ticker->sell,
                    'amount' => $arr[self::BUY_TYPE][1]);
                $client -> tradeApi($params);
                file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：")."pb---".($arr[self::BUY_TYPE][2]?'yes':'no').PHP_EOL, 8);
            }

            if ($arr[self::SEAL_TYPE][0] == 1 && $arr[self::SEAL_TYPE][1] > 0){
                $params = array('api_key' => $this->api_key,
                    'symbol' => $this->symbol,
                    'contract_type' => $this->contract_type,
                    'type' => self::TRADE_CLOSE_SEAL,
                    'price' => $cur_trade_info->ticker->buy,
                    'amount' => $arr[self::SEAL_TYPE][1]);
                $client -> tradeApi($params);
                file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：")."ps---".($arr[self::SEAL_TYPE][2]?'yes':'no').PHP_EOL, 8);
//                print_r("平空单");
//                var_dump($result);
            }
        }

        return $cur_trade_info;
    }

    //返回是否需要平仓
    private function _handle_order_plain_a($account_info)
    {
        //[0, 0] : 第一个值为是否平仓，第二个值为平仓数量
        $arr = [self::BUY_TYPE =>[0, 0, 0], self::SEAL_TYPE => [0, 0, 0]];
        if ($account_info->buy_profit_lossratio > self::WIN_PERCENT){
            //盈利
            $arr[self::BUY_TYPE][0] = 1;
            $arr[self::BUY_TYPE][1] = $account_info->buy_amount;
            $arr[self::BUY_TYPE][2] = 1;
        }elseif($account_info->buy_profit_lossratio < self::LOST_PERCENT){
            //割肉
            $arr[self::BUY_TYPE][0] = 1;
            $arr[self::BUY_TYPE][1] = $account_info->buy_amount;
        }

        if ($account_info->sell_profit_lossratio > self::WIN_PERCENT){
            //盈利
            $arr[self::SEAL_TYPE][0] = 1;
            $arr[self::SEAL_TYPE][1] = $account_info->sell_amount;
            $arr[self::SEAL_TYPE][2] = 1;
        }elseif($account_info->sell_profit_lossratio < self::LOST_PERCENT){
            //割肉
            $arr[self::SEAL_TYPE][0] = 1;
            $arr[self::SEAL_TYPE][1] = $account_info->sell_amount;
        }

        return $arr;
    }

    private function _create_order(OKCoin $client, $cur_data)
    {
        //根据保存的数据先判断当前市场是否活跃，选择激进还是保守，
            //激进下重单
            //保守下轻单

        //判断是上涨--横盘--下跌趋势

        //可以添加随机比例，然后看下做多做空

        if (!$this->is_start){
            if (count($this->tlist) > 20){
                $this->is_start = true;
            }
            return ;
        }

        $buy = 0;
        $sale = 0;
        foreach ($this->tlist as $k => $v){
            if ($v['d_value'] > 0){
                $buy += $v['d_value'];
            }else{
                $sale += $v['d_value'];
            }
        }

        //可以判断他们的比例，然后根据比例给个随机数
        $c = (10000*$buy - 10000*$sale);
        if (rand(0, $c) < 10000*$buy){
            $type = self::TRADE_OPEN_BUY;
            $price = $cur_data->ticker->sell;
            //print_r("下多单");
            file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：")."bb".PHP_EOL, 8);
        }else{
            $type = self::TRADE_OPEN_SEAL;
            $price = $cur_data->ticker->buy;
            //print_r("下空单");
            file_put_contents('log/'.date("Ymd").'.txt', date("YmdHis：")."bs".PHP_EOL, 8);
        }

        $params = array('api_key' => $this->api_key,
            'symbol' => $this->symbol,
            'contract_type' => $this->contract_type,
            'type' => $type,
            'price' => $price,
            'amount' => "1");
        $result = $client -> tradeApi($params);
        //var_dump($result);
    }

    private function _account_info(OKCoin $client)
    {
//        buy_amount:多仓数量
//        buy_available:多仓可平仓数量
//        buy_bond:多仓保证金
//        buy_flatprice:多仓强平价格
//        buy_profit_lossratio:多仓盈亏比
//        buy_price_avg:开仓平均价
//        buy_price_cost:结算基准价
//        buy_profit_real:多仓已实现盈余
//        contract_id:合约id
//        contract_type:合约类型
//        create_date:创建日期
//        sell_amount:空仓数量
//        sell_available:空仓可平仓数量
//        sell_bond:空仓保证金
//        sell_flatprice:空仓强平价格
//        sell_profit_lossratio:空仓盈亏比
//        sell_price_avg:开仓平均价
//        sell_price_cost:结算基准价
//        sell_profit_real:空仓已实现盈余
//        symbol:btc_usd   ltc_usd    eth_usd    etc_usd    bch_usd
//        lever_rate: 杠杆倍数

        $params = array('api_key' => $this->api_key, 'symbol' => $this->symbol, 'contract_type' => $this->contract_type, 'type' => 1);
        $result = $client -> singleBondPositionFutureApi($params);

        return $result->holding[0]??null;
    }

    private function _make_queue($data)
    {
        //$data = ['当前时间', '当前价格', '减去上一次所得的值']
        $this->tlist[] = $data;
        if (count($this->tlist) > $this->max_queue){
            array_shift($this->tlist);
        }
    }
}