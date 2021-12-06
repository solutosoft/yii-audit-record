<?php

namespace solutosoft\auditrecord;

use paulzi\jsonBehavior\JsonBehavior;
use yii\db\ActiveRecord;

class Audit extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            [
                'class' => JsonBehavior::class,
                'attributes' => ['data'],
            ],
        ];
    }

    /**
     * @return void
     */
    public static function tableName()
    {
        return '{{%audit}}';
    }
}
