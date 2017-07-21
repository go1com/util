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
}
