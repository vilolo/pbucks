<?php
/**
 * @copyright Copyright (c) 2017 Kaicai Media LLC
 * @author luoweifeng <luoweifeng@kaicaimedia.com>
 */

namespace console\controllers;

use backend\controllers\src\ok\OKCoin;
use PHPUnit\Framework\Exception;

class ShooterController extends BaseController
{
    const ACCESS_INTERVAL = 500000;   //访问间隔

    public function actionIndex()
    {
        $client = new OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->secret_key));

        while (true){
            usleep(self::ACCESS_INTERVAL);

            try{
                
            }catch (Exception $e){
                $this->log($e->getMessage());
            }

        }

    }
}