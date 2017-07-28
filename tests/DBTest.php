<?php

namespace go1\util\schema\tests;

use go1\util\DB;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;

class DBTest extends UtilTestCase
{
    public function testConnectionOptions()
    {
        putenv('_DOCKER_FOO_DB_NAME=foo_db');
        putenv('_DOCKER_FOO_DB_USERNAME=foo_username');
        putenv('_DOCKER_FOO_DB_PASSWORD=foo_password');
        putenv('_DOCKER_FOO_DB_SLAVE=slave.foo.com');
        putenv('_DOCKER_FOO_DB_HOST=foo.com');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $foo = DB::connectionOptions('foo');
        $this->assertEquals('pdo_mysql', $foo['driver']);
        $this->assertEquals('foo_db', $foo['dbname']);
        $this->assertNotEquals('slave.foo.com', $foo['host']);
        $this->assertEquals('foo_username', $foo['user']);
        $this->assertEquals('foo_password', $foo['password']);
        $this->assertEquals(3306, $foo['port']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $foo = DB::connectionOptions('foo');
        $this->assertEquals('slave.foo.com', $foo['host']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $foo = DB::connectionOptions('foo', true);
        $this->assertEquals('slave.foo.com', $foo['host']);
    }

    public function testCacheSet()
    {
        $cache = &DB::cache(self::class, []);
        $cache['foo'] = 'bar';

        $this->assertEquals(['foo' => 'bar'], DB::cache(self::class));
    }

    public function testCacheRetrieval()
    {
        $this->assertEquals(['foo' => 'bar'], DB::cache(self::class));
    }

    public function testCacheRetrievalReset()
    {
        $this->assertEquals([], DB::cache(self::class, null, true));
    }

    public function testMerge()
    {
        DB::merge($this->db, 'gc_user', [], $dataUser = [
            'id'         => $userId = 99,
            'first_name' => 'Nikk',
            'last_name'  => 'Nguyen',
            'mail'       => 'user@foo.com',
            'uuid'       => 'xxx',
            'instance'   => 'foo.com',
            'password'   => 'yyy',
            'created'    => time(),
            'access'     => time(),
            'login'      => time(),
            'timestamp'  => time(),
            'status'     => 1,
            'data'       => json_encode(null),
        ]);
        $originalUser = (array) UserHelper::load($this->db, $userId);

        $this->assertArraySubset($dataUser, $originalUser);

        DB::merge(
            $this->db, 'gc_user',
            [
                'id' => $userId,
            ],
            $changedData = [
                'mail'       => 'changed@foo.com',
                'first_name' => 'Phuc',
                'instance'   => 'bar.com',
            ]
        );
        $user = (array) UserHelper::load($this->db, $userId);

        $this->assertEquals($changedData, array_diff($user, $originalUser));
    }

    public function testLoad()
    {
        $fooUserId = $this->createUser($this->db, [
            'mail' => 'foo@foo.com',
            'data' => $fooData = ['foo' => 'bar'],
        ]);
        $barUserId = $this->createUser($this->db, [
            'mail' => 'bar@foo.com',
        ]);

        $fooUserObj = DB::load($this->db, 'gc_user', $fooUserId, DB::OBJ);
        $barUserObj = DB::load($this->db, 'gc_user', $barUserId, DB::OBJ);

        $this->assertInternalType('object', $fooUserObj);
        $this->assertEquals((object) $fooData, $fooUserObj->data);
        $this->assertInternalType('object', $barUserObj);
        $this->assertEquals([], $barUserObj->data);

        $fooUserArr = DB::load($this->db, 'gc_user', $fooUserId, DB::ASS);
        $barUserArr = DB::load($this->db, 'gc_user', $barUserId, DB::ASS);

        $this->assertInternalType('array', $fooUserArr);
        $this->assertEquals($fooData, $fooUserArr['data']);
        $this->assertInternalType('array', $barUserArr);
        $this->assertEquals([], $barUserArr['data']);
    }

    public function testLoadMultiple()
    {
        $fooUserId = $this->createUser($this->db, [
            'mail' => 'foo@foo.com',
            'data' => $fooData = ['foo' => 'bar'],
        ]);
        $barUserId = $this->createUser($this->db, [
            'mail' => 'bar@foo.com',
        ]);

        $usersObj = DB::loadMultiple($this->db, 'gc_user', [$fooUserId, $barUserId], DB::OBJ);

        $this->assertCount(2, $usersObj);
        $this->assertInternalType('object', $usersObj[0]);
        $this->assertEquals((object) $fooData, $usersObj[0]->data);
        $this->assertInternalType('object', $usersObj[1]);
        $this->assertEquals([], $usersObj[1]->data);

        $usersArr = DB::loadMultiple($this->db, 'gc_user', [$fooUserId, $barUserId], DB::ASS);

        $this->assertCount(2, $usersArr);
        $this->assertInternalType('array', $usersArr[0]);
        $this->assertEquals($fooData, $usersArr[0]['data']);
        $this->assertInternalType('array', $usersArr[1]);
        $this->assertEquals([], $usersArr[1]['data']);
    }
}
