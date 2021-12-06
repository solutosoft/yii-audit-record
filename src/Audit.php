<?php

namespace solutosoft\auditrecord;

use Yii;
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
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['classname'], $fields['record_id']);

        return $fields;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }

    /**
     * @return void
     */
    public static function tableName()
    {
        return '{{%audit}}';
    }
}
