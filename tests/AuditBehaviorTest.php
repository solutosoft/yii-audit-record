<?php

namespace solutosoft\auditrecord\tests;

use Yii;
use solutosoft\auditrecord\tests\models\Person;
use yii\db\ActiveRecord;

class AuditBehaviorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $identity = Person::findIdentity(1);
        Yii::$app->getUser()->login($identity);
    }

    public function testStoreChanges()
    {
        $updated_at =  date('Y-m-d H:i:s');
        $person = new Person([
            'name' => 'Steve',
            'birthDate' => '1955-02-24',
            'salary' => 1000.50,
            'updated_at' => $updated_at
        ]);

        $person->save();
        $row = $person->history[0];

        $this->assertEquals(ActiveRecord::OP_INSERT, $row->operation);

        $this->assertEquals([
            'id' => ['new' => 2],
            'name' => ['new' => 'Steve'],
            'birthDate' => ['new' => '1955-02-24'],
            'salary' => ['new' => 1000.50],
            'updated_at' => ['new' => $updated_at]
        ], $row->data->toArray());



        $person->birthDate = '1983-04-20';
        $person->save();
        $row = $person->history[0];

        $this->assertEquals(ActiveRecord::OP_UPDATE, $row->operation);

        $this->assertEquals([
            'birthDate' => ['old' => '1955-02-24', 'new' => '1983-04-20'],
        ], $row->data->toArray());

        $person->delete();
        $row = $person->history[0];

        $this->assertEquals(ActiveRecord::OP_DELETE, $row->operation);

        $this->assertEquals([
            'id' => ['old' => 2],
            'name' => ['old' => 'Steve'],
            'birthDate' => ['old' => '1983-04-20'],
            'salary' => ['old' => 1000.50],
            'updated_at' => ['old' => $updated_at]
        ], $row->data->toArray());
    }

}
