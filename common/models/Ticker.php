<?php
/**
 * @copyright Copyright (c) 2017 Kaicai Media LLC
 * @author luoweifeng <luoweifeng@kaicaimedia.com>
 */

namespace common\models;

class Ticker
{
    //{"date":"1529918317","ticker":{"high":83.965,"vol":4833072,"day_high":0,"last":80.46,"low":72.748,"contract_id":201806290010035,"buy":80.424,"sell":80.496,"coin_vol":0,"day_low":0,"unit_amount":10}}
    public $date;
    public $high;
    public $vol;
    public $day_high;
    public $last;
    public $low;
    public $contract_id;
    public $buy;
    public $sell;
    public $coin_vol;
    public $day_low;
    public $unit_amount;
}