<?php

namespace solutosoft\auditrecord\tests;

use Yii;
use solutosoft\auditrecord\Audit;
use solutosoft\auditrecord\tests\models\User;
use yii\base\Event;
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
        $user = new User([
            'name' => 'Steve',
            'birthDate' => '1955-02-24',
            'salary' => 1000.50,
            'updated_at' => $updated_at
        ]);

        $user->save();
        $row = $user->history[0];

        $this->assertEquals(ActiveRecord::OP_INSERT, $row->operation);

        $this->assertEquals([
            'id' => ['new' => 2],
            'name' => ['new' => 'Steve'],
            'birthDate' => ['new' => '1955-02-24'],
            'salary' => ['new' => 1000.50],
            'updated_at' => ['new' => $updated_at]
        ], $row->data);


        $user->birthDate = '1983-04-20';
        $user->save();
        $row = $user->history[0];

        $this->assertEquals(ActiveRecord::OP_UPDATE, $row->operation);

        $this->assertEquals([
            'birthDate' => ['old' => '1955-02-24', 'new' => '1983-04-20'],
        ], $row->data);

        $user->delete();
        $row = $user->history[0];

        $this->assertEquals(ActiveRecord::OP_DELETE, $row->operation);

        $this->assertEquals([
            'id' => ['old' => 2],
            'name' => ['old' => 'Steve'],
            'birthDate' => ['old' => '1983-04-20'],
            'salary' => ['old' => 1000.50],
            'updated_at' => ['old' => $updated_at]
        ], $row->data);
    }


    public function testFields()
    {
        $user = new User([
            'name' => 'Steve',
            'birthDate' => '1955-02-24',
            'salary' => 1000.50,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $user->save();
        $fields = $user->history[0]->toArray();

        $this->assertArrayNotHasKey('classname', $fields);
        $this->assertArrayNotHasKey('record_id', $fields);
    }


    public function testUserRelationEvent()
    {
        Event::on(Audit::class, Audit::EVENT_USER_RELATION, function ($event) {
            $this->assertNotNull($event->query);
        });

        $user = new User([
            'name' => 'Steve'
        ]);

        $user->save();
        $user->history[0]->user;
    }

}

