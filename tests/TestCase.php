<?php

namespace solutosoft\auditrecord\tests;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->setupDatabase();
    }

    protected function tearDown(): void
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [])
    {
        new \yii\web\Application(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                    /*'dsn' => 'mysql:host=mysql;dbname=audit-record',
                    'username' => 'root',
                    'password' => 'root',
                    'charset' => 'utf8',
                    'enableSchemaCache' => false*/

                ],
                'user' => [
                    'identityClass' => 'Soluto\Tests\Data\Person',
                    'enableSession' => false
                ],
                'request' => [
                    'cookieValidationKey' => 'audit-cookie-key',
                    'scriptFile' => __DIR__ .'/index.php',
                    'scriptUrl' => '/index.php',
                ]
            ]

        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupDatabase()
    {
        $db = Yii::$app->getDb();

        $db->createCommand()->createTable('audit', [
            'id' => 'pk',
            'user_id' => 'integer',
            'record_id' => 'string',
            'classname' => 'date',
            'operation' => 'integer',
            'data' => 'text',
            'created_at' => 'integer'
        ])->execute();

        $db->createCommand()->createTable('person', [
            'id' => 'pk',
            'name' => 'string',
            'birthDate' => 'date',
            'salary' => 'decimal(18,2)',
            'updated_at' => 'datetime'
        ])->execute();

        $db->getSchema()->insert('person', ['name' => 'User']);
    }
}
