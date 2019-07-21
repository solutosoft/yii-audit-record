<?php

namespace Soluto\AuditRecord\Tests;

use Soluto\AuditRecord\Tests\Data\Person;
use Yii;
use yii\db\Query;
use yii\helpers\Json;
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
        $row = $this->getLastRow();

        $this->assertEquals(ActiveRecord::OP_INSERT, $row['operation']);

        $this->assertEquals([
            'id' => ['new' => 2],
            'name' => ['new' => 'Steve'],
            'birthDate' => ['new' => '1955-02-24'],
            'salary' => ['new' => 1000.50],
            'updated_at' => ['new' => $updated_at]
        ], Json::decode($row['data']));

        $person->birthDate = '1983-04-20';
        $person->save();
        $row = $this->getLastRow();

        $this->assertEquals(ActiveRecord::OP_UPDATE, $row['operation']);

        $this->assertEquals([
            'birthDate' => ['old' => '1955-02-24', 'new' => '1983-04-20'],
        ], Json::decode($row['data']));


        $person->delete();
        $row = $this->getLastRow();

        $this->assertEquals(ActiveRecord::OP_DELETE, $row['operation']);

        $this->assertEquals([
            'id' => ['new' => 2],
            'name' => ['new' => 'Steve'],
            'birthDate' => ['new' => '1983-04-20'],
            'salary' => ['new' => 1000.50],
            'updated_at' => ['new' => $updated_at]
        ], Json::decode($row['data']));
    }

    private function getLastRow()
    {
        return (new Query())
            ->from('audit')
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }



}