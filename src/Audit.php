<?php

namespace solutosoft\auditrecord;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

class Audit extends ActiveRecord
{
    const EVENT_USER_RELATION = 'userRelation';

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

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if ($name === 'data') {
            $this->setAttribute($name, Json::encode($value));
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $result = parent::__get($name);
        return $name === 'data' ? Json::decode($result) : $result;
    }

}
