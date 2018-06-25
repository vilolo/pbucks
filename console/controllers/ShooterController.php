<?php
/**
 * @copyright Copyright (c) 2017 Kaicai Media LLC
 * @author luoweifeng <luoweifeng@kaicaimedia.com>
 */

namespace console\controllers;

use console\controllers\strategy\InA;

class ShooterController extends BaseController
{
    public function actionIndex()
    {
        $in = new InA();

    }
}