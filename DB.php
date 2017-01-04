<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use go1\app\App;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse;

class DB
{
    const OBJ      = PDO::FETCH_OBJ;
    const INTEGER  = PDO::PARAM_INT;
    const INTEGERS = Connection::PARAM_INT_ARRAY;
    const STRING   = PDO::PARAM_STR;
    const STRINGS  = Connection::PARAM_STR_ARRAY;

    public static function connectionOptions(string $name): array
    {
        $prefix = strtoupper(class_exists(App::class, false) ? "{$name}_DB" : "_DOCKER_{$name}_DB");
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $host = ('go1' === $name) ? 'hostmasterdb.csb6wde17f7d.ap-southeast-2.rds.amazonaws.com' : 'microservice.csb6wde17f7d.ap-southeast-2.rds.amazonaws.com';
        $slave = true # We can't use the slave connection for now.
            ? getenv("{$prefix}_HOST")
            : (in_array($method, ['GET', 'OPTIONS']) ? getenv("{$prefix}_MASTER") : getenv("{$prefix}_SLAVE"));

        $dbName = "{$name}_dev";
        if ('go1' === $name) {
            $dbName = in_array(getenv('_DOCKER_ENV'), ['staging', 'prod']) ? 'gc_go1' : 'dev_go1';
        }

        return [
            'driver'        => 'pdo_mysql',
            'dbname'        => getenv("{$prefix}_NAME") ?: $dbName,
            'host'          => $slave ?: $host,
            'user'          => getenv("{$prefix}_USERNAME") ?: 'gc_dev',
            'password'      => getenv("{$prefix}_PASSWORD") ?: 'gc_dev#2016',
            'port'          => getenv("{$prefix}_PORT") ?: '3306',
            'driverOptions' => [1002 => 'SET NAMES utf8'],
        ];
    }

    public static function safeThread(Connection $db, string $threadName, int $timeout, callable $callback)
    {
        try {
            $sqlite = 'sqlite' === $db->getDatabasePlatform()->getName();
            !$sqlite && $db->executeQuery('DO GET_LOCK("' . $threadName . '", ' . $timeout . ')');
            $callback($db);
        }
        finally {
            !$sqlite && $db->executeQuery('DO RELEASE_LOCK("' . $threadName . '")');
        }
    }

    /**
     * @param Connection          $db
     * @param callable|callable[] $callbacks
     * @return JsonResponse
     */
    public static function install(Connection $db, array $callbacks): JsonResponse
    {
        $db->transactional(
            function (Connection $db) use (&$callbacks) {
                $compare = new Comparator;
                $schemaManager = $db->getSchemaManager();
                $schema = $schemaManager->createSchema();
                $originSchema = clone $schema;

                $callbacks = is_array($callbacks) ? $callbacks : [$callbacks];
                foreach ($callbacks as &$callback) {
                    $callback($schema);
                }

                $diff = $compare->compare($originSchema, $schema);
                foreach ($diff->toSql($db->getDatabasePlatform()) as $sql) {
                    try {
                        $db->executeQuery($sql);
                    }
                    catch (TableExistsException $e) {
                    }
                }
            }
        );

        return new JsonResponse([], 200);
    }
}
