<?php

namespace solutosoft\auditrecord;

use yii\base\Event;

class AuditRelationEvent extends Event
{
    /**
     * The query instance for relationship
     * @var \yii\db\ActiveQuery
     */
    public $query;
}
