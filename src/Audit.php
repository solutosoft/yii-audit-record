<?php

namespace solutosoft\auditrecord;

use Yii;
use paulzi\jsonBehavior\JsonBehavior;
use yii\db\ActiveRecord;

class Audit extends ActiveRecord
{
    const EVENT_USER_RELATION = 'userRelation';


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
        $query = $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);

        $this->trigger(self::EVENT_USER_RELATION, new AuditRelationEvent(['query' => $query]));

        return $query;
    }

    /**
     * @return void
     */
    public static function tableName()
    {
        return '{{%audit}}';
    }
}
