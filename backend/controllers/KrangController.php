<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/6/10
 * Time: 1:04
 */

namespace backend\controllers;

use backend\controllers\src\ok\OKCoin;
use yii\base\Controller;

class KrangController extends Controller
{
    const API_KEY = "85407212-bb1c-4de1-87be-2c34a4df36c5";
    const SECRET_KEY = "60AE7CE93B827ED29B25E97CAB9E3EFC";
    const CONTRACT_TYPE = "this_week";
    const SYMBOL = "ltc_usd";

    public function actionIndex()
    {
        //OKCoin DEMO 入口
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication(self::API_KEY, self::SECRET_KEY));

        //获取OKCoin行情（盘口数据）
        $params = array('symbol' => self::SYMBOL, 'contract_type' => self::CONTRACT_TYPE);
        $result = $client -> tickerApi($params);
        print_r($result);

        //获取用户信息
        $params = array('api_key' => self::API_KEY);
        $result = $client -> userinfoApi($params);
        print_r($result);
    }
}