<?php

namespace solutosoft\auditrecord\tests\models;

use solutosoft\auditrecord\AuditBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

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
class User extends ActiveRecord implements IdentityInterface
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
        return 'users';
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
