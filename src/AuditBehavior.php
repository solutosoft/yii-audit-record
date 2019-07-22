<?php

namespace Soluto\AuditRecord;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class AuditBehavior extends Behavior
{
    /**
     * @var \yii\db\ActiveRecord the owner of this behavior.
     */
    public $owner;

    /**
     * @var array list of attributes that should not store logdata.
     */
    public $except = [];

    /**
     * The handled operations
     * Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
     * @var integer
     */
    public $operations = ActiveRecord::OP_ALL;

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'Soluto\AuditRecord\JsonSerializer';

    /**
     * The `created_at` field value
     * In case, when the value is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    public $created_at;

    /**
     * @var string name of the DB table to store log content. Defaults to "record_audit".
     */
    public $tableName = '{{%audit}}';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'store',
            ActiveRecord::EVENT_AFTER_UPDATE => 'store',
            ActiveRecord::EVENT_AFTER_DELETE => 'store'
        ];
    }

    /**
     * Store changed attributes
     * @param \yii\db\AfterSaveEvent $event
     */
    public function store($event)
    {
        switch ($event->name) {
            case ActiveRecord::EVENT_AFTER_INSERT:
                $operation = ActiveRecord::OP_INSERT;
                break;

            case ActiveRecord::EVENT_AFTER_UPDATE:
                $operation = ActiveRecord::OP_UPDATE;
                break;

            default:
                $operation = ActiveRecord::OP_DELETE;
        }

        if (!($operation & $this->operations)) {
            return;
        }

        $update = $operation === ActiveRecord::OP_UPDATE;
        $attributes = ($update) ? $event->changedAttributes : $this->owner->getAttributes();
        $attributeNames = array_keys($attributes);

        foreach (array_keys($attributeNames) as $name) {
            if (in_array($name, $this->except)) {
                unset($attributes[$name]);
            }
        }

        $user = Yii::$app->getUser();
        if ($user->getIsGuest() || empty($attributes)) {
            return;
        }

        $data = [];
        foreach ($attributeNames as $name) {
            $key = $operation === ActiveRecord::OP_DELETE ? 'old' : 'new';
            $data[$name] = [$key => $this->owner->{$name}];

            if ($update) {
                $data[$name]['old'] = $attributes[$name];
            }
        }


        $db = Yii::$app->getDb();
        $tableName = $db->quoteTableName($this->tableName);
        $db->createCommand()
            ->insert($tableName, [
                'user_id' => $user->id,
                'record_id' => $this->owner->id,
                'operation' => $operation,
                'classname' => get_class($this->owner),
                'data' => $this->serializeData($data),
                'created_at' => $this->getCreatedAt()
            ])
            ->execute();

    }

    /**
     * Serializes given attributes into a string.
     * @param array $data attributes to be serialized in format: name => value
     * @return string serialized attributes.
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    /**
     * Returns the value for created_at property.
     * to the attributes corresponding to the triggering event.
     * @return mixed the attribute value
     */
    protected function getCreatedAt()
    {
        if ($this->created_at === null) {
            return time();
        }

        if ($this->created_at instanceof Closure || (is_array($this->created_at) && is_callable($this->created_at))) {
            return call_user_func($this->created_at, $this);
        }

        return $this->created_at;
    }


}
