<?php

namespace Soluto\AuditRecord\Tests\Data;

use yii\db\ActiveRecord;
use Soluto\AuditRecord\AuditBehavior;
use yii\web\IdentityInterface;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * @property string $firstName
 * @property string $lastName
 * @property string $birthDate
 * @property double $salary
 * @property integer $profile_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $tenant_id
 * @property Profile[] $profile
 * @property Contact[] $contacts
 * @property Tag[] $tags
 */
class Person extends ActiveRecord implements IdentityInterface
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'audit' => [
                'class' => AuditBehavior::class
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'person';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['person_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['birthDate'], 'date', 'type' => 'datetime', 'format' => 'php:Y-m-d'],
            [['salary'], 'number'],
            [['updated_at'], 'date', 'type' => 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

}
