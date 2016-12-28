<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse;

class DB
{
    const OBJ      = PDO::FETCH_OBJ;
    const INTEGER  = PDO::PARAM_INT;
    const INTEGERS = Connection::PARAM_INT_ARRAY;
    const STRING   = PDO::PARAM_STR;
    const STRINGS  = Connection::PARAM_STR_ARRAY;

    public static function host($masterKey = 'RDS_HOSTNAME', $slaveKey = 'RDS_HOSTNAME_SLAVE', $default = 'microservice.csb6wde17f7d.ap-southeast-2.rds.amazonaws.com')
    {
        if (true) {
            return getenv($masterKey) ?: $default; # We cant 'use the slave connection for now, but soon we can.
        }

        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $host = in_array($method, ['GET', 'OPTIONS']) ? getenv($slaveKey) : getenv($masterKey);

        return $host ?: $default;
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
