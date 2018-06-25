<?php
/**
 * @copyright Copyright (c) 2017 Kaicai Media LLC
 * @author luoweifeng <luoweifeng@kaicaimedia.com>
 */

namespace console\controllers;

use Yii;
use yii\base\Controller;

class SmltController extends BaseController
{
    public function actionIndex()
    {
        $db = new \LevelDB($this->db_path);
        $it = new \LevelDBIterator($db);
        while($it->valid()) {
            var_dump($it->key() . " => " . $it->current() . "\n");
        }
    }
}