<?php

use yii\db\Migration;

/**
 * Handles the creation of table `sys_config`.
 */
class m180610_161431_create_sys_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sys_config}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment("配置名"),
            'code' => $this->string(30)->comment("第二标识"),
            'value' => $this->string(30)->comment("值"),
            'type' => $this->tinyInteger(2)->unsigned()->comment("类型。1=交易控制配置"),
            'status' => $this->tinyInteger(1)->unsigned()->defaultValue(1)->comment("状态。0=停用，1=启用"),
            'remark' => $this->string(100)->comment('备注')
        ]);

        $this->batchInsert('{{%sys_config}}', ['id', 'name', 'value', 'type', 'remark'], [
            [1, 'is_run', 1, 1, '是否开启'],
            [2, 'is_new_order', 1, 1, '是否允许下新订单'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sys_config}}');
    }
}
