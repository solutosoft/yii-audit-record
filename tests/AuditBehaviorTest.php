<?php

namespace solutosoft\auditrecord\tests;

use Yii;
use solutosoft\auditrecord\Audit;
use solutosoft\auditrecord\tests\models\User;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class AuditBehaviorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $identity = User::findIdentity(1);
        Yii::$app->getUser()->login($identity);
    }

    public function testStoreChanges()
    {
        $updated_at =  date('Y-m-d H:i:s');
        $User = new User([
            'name' => 'Steve',
            'birthDate' => '1955-02-24',
            'salary' => 1000.50,
            'updated_at' => $updated_at
        ]);

        $User->save();
        $row = $User->history[0];

        $this->assertEquals(ActiveRecord::OP_INSERT, $row->operation);

        $this->assertEquals([
            'id' => ['new' => 2],
            'name' => ['new' => 'Steve'],
            'birthDate' => ['new' => '1955-02-24'],
            'salary' => ['new' => 1000.50],
            'updated_at' => ['new' => $updated_at]
        ], $row->data->toArray());


        $User->birthDate = '1983-04-20';
        $User->save();
        $row = $User->history[0];

        $this->assertEquals(ActiveRecord::OP_UPDATE, $row->operation);

        $this->assertEquals([
            'birthDate' => ['old' => '1955-02-24', 'new' => '1983-04-20'],
        ], $row->data->toArray());

        $User->delete();
        $row = $User->history[0];

        $this->assertEquals(ActiveRecord::OP_DELETE, $row->operation);

        $this->assertEquals([
            'id' => ['old' => 2],
            'name' => ['old' => 'Steve'],
            'birthDate' => ['old' => '1983-04-20'],
            'salary' => ['old' => 1000.50],
            'updated_at' => ['old' => $updated_at]
        ], $row->data->toArray());
    }


    public function testFields()
    {
        $User = new User([
            'name' => 'Steve',
            'birthDate' => '1955-02-24',
            'salary' => 1000.50,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $User->save();
        $fields = $User->history[0]->toArray();

        $this->assertArrayNotHasKey('classname', $fields);
        $this->assertArrayNotHasKey('record_id', $fields);
    }


    public function testUserRelationEvent()
    {
        Event::on(Audit::class, Audit::EVENT_USER_RELATION, function ($event) {
            $this->assertNotNull($event->query);
        });

        $User = new User([
            'name' => 'Steve'
        ]);

        $User->save();
        $User->history[0]->user;
    }

}

