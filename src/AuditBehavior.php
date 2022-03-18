<?php

namespace solutosoft\auditrecord;

use Closure;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property-read array $history Array of record changes records indexed by relation names. This property
 */
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
     * The `created_at` field value
     * In case, when the value is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
     * will be used as value.
     */
    public $createdAt;

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
        $operation = ActiveRecord::OP_DELETE;

        if ($event) {
            if ($event->name === ActiveRecord::EVENT_AFTER_INSERT) {
                $operation = ActiveRecord::OP_INSERT;
            } elseif ($event->name === ActiveRecord::EVENT_AFTER_UPDATE) {
                $operation = ActiveRecord::OP_UPDATE;
            }
        }

        if (!($operation & $this->operations)) {
            return;
        }

        $update = $operation === ActiveRecord::OP_UPDATE;
        $attributes = ($update) ? $event->changedAttributes : $this->owner->getAttributes();
        $attributeNames = array_keys($attributes);

        foreach ($attributeNames as $name) {
            if (in_array($name, $this->except)) {
                unset($attributes[$name]);
            }
        }

        $user = Yii::$app->getUser();
        if ($user->getIsGuest() || empty($attributes)) {
            return;
        }

        $data = [];
        foreach ($attributes as $name => $old) {
            $value = $this->owner->{$name};
            $changed = $old != $value;

            if ($operation === ActiveRecord::OP_INSERT || $changed) {
                $data[$name] = ['new' => $value];

                if ($update) {
                    $data[$name]['old'] = $old;
                }
            } elseif ($operation === ActiveRecord::OP_DELETE) {
                $data[$name] = ['old' => $value];
            }
        }

        if ($data) {
            $audit = new Audit([
                'user_id' => $user->id,
                'record_id' => $this->owner->id,
                'operation' => $operation,
                'classname' => get_class($this->owner),
                'created_at' => $this->extractCreatedAt()
            ]);

            $audit->data = $data;
            $audit->save(false);
        }
    }

    /**
     * Create instance for query purpose.
     * @return \yii\db\ActiveQuery
     */
    public function getHistory()
    {
        return Audit::find()
            ->where([
                'record_id' => $this->owner->id,
                'classname' => get_class($this->owner)
            ])
            ->orderBy(['id' => SORT_DESC]);
    }

    /**
     * PHP getter magic method.
     * @param string $name property name
     * @return mixed property value
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        return $value instanceof Query ? $value->all() : $value;
    }

    /**
     * Returns the value for created_at property.
     * to the attributes corresponding to the triggering event.
     * @return mixed the attribute value
     */
    protected function extractCreatedAt()
    {
        if ($this->createdAt === null) {
            return time();
        }

        if ($this->createdAt instanceof Closure || (is_array($this->createdAt) && is_callable($this->createdAt))) {
            return call_user_func($this->createdAt, $this);
        }

        return $this->createdAt;
    }
}
