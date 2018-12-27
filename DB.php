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
    const ARR      = PDO::FETCH_ASSOC;
    const COL      = PDO::FETCH_COLUMN;
    const INTEGER  = PDO::PARAM_INT;
    const INTEGERS = Connection::PARAM_INT_ARRAY;
    const STRING   = PDO::PARAM_STR;
    const STRINGS  = Connection::PARAM_STR_ARRAY;

    public static function connectionOptions(string $name, $forceSlave = false, $forceMaster = false): array
    {
        if (function_exists('__db_connection_options')) {
            return __db_connection_options($name);
        }

        $prefix = strtoupper("{$name}_DB");
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $slave = self::getEnvByPriority(["{$prefix}_HOST", 'RDS_DB_HOST', 'DEV_DB_HOST']);

        if (('GET' === $method) || $forceSlave) {
            if (!$forceMaster) {
                $slave = self::getEnvByPriority(["{$prefix}_SLAVE", 'RDS_DB_SLAVE', 'DEV_DB_SLAVE']) ?: $slave;
            }
        }

        $isDevEnv = !in_array(self::getEnvByPriority(['_DOCKER_ENV', 'ENV']), ['staging', 'production']);
        $dbName = $isDevEnv ? "{$name}_dev" : "{$name}_prod";
        if ('go1' === $name) {
            $dbName = $isDevEnv ? 'dev_go1' : 'gc_go1';
        }

        return [
            'driver'        => 'pdo_mysql',
            'dbname'        => getenv("{$prefix}_NAME") ?: $dbName,
            'host'          => $slave,
            'user'          => self::getEnvByPriority(["{$prefix}_USERNAME", 'RDS_DB_USERNAME', 'DEV_DB_USERNAME']),
            'password'      => self::getEnvByPriority(["{$prefix}_PASSWORD", 'RDS_DB_PASSWORD', 'DEV_DB_PASSWORD']),
            'port'          => getenv("{$prefix}_PORT") ?: '3306',
            'driverOptions' => [1002 => 'SET NAMES utf8'],
        ];
    }

    private static function getEnvByPriority(array $names)
    {
        foreach ($names as $name) {
            if ($value = getenv($name)) {
                return $value;
            }
        }
    }

    public static function transactional(Connection $db, callable $callback)
    {
        $return = null;

        $db->transactional(function (Connection $db) use (&$return, &$callback) {
            $return = call_user_func($callback, $db);
        });

        return $return;
    }

    public static function safeThread(Connection $db, string $threadName, int $timeout, callable $callback)
    {
        try {
            $sqlite = 'sqlite' === $db->getDatabasePlatform()->getName();
            !$sqlite && $db->executeQuery('DO GET_LOCK("' . $threadName . '", ' . $timeout . ')');

            return $callback($db);
        } finally {
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
                    } catch (TableExistsException $e) {
                    }
                }
            }
        );

        return new JsonResponse([], 204);
    }

    public static function &cache($name, $defaultValue = null, $reset = false)
    {
        static $data = [], $default = [];

        if (isset($data[$name]) || array_key_exists($name, $data)) {
            if ($reset) {
                $data[$name] = $default[$name];
            }

            return $data[$name];
        }

        if (isset($name)) {
            if ($reset) {
                return $data;
            }
            $default[$name] = $data[$name] = $defaultValue;

            return $data[$name];
        }

        foreach ($default as $name => $value) {
            $data[$name] = $value;
        }

        return $data;
    }

    public static function merge(Connection $db, string $table, array $keys, array $fields): int
    {
        $find = $db
            ->createQueryBuilder()
            ->select('1')
            ->from($table);

        foreach ($keys as $k => $v) {
            $find
                ->andWhere("$k = :$k")
                ->setParameter(":$k", $v);
        }

        return $find->execute()->fetch(DB::OBJ)
            ? $db->update($table, $fields, $keys)
            : $db->insert($table, $fields);
    }

    public static function loadMultiple(Connection $db, string $tableName, array $ids, string $fetchMode = DB::OBJ)
    {
        $q = $db->createQueryBuilder();
        $q = $q
            ->select('*')
            ->from($tableName)
            ->where($q->expr()->in('id', ':ids'))
            ->setParameter(':ids', $ids, DB::INTEGERS)
            ->execute();

        $entities = [];
        while ($entity = $q->fetch($fetchMode)) {
            if (DB::OBJ == $fetchMode) {
                $data = &$entity->data ?? null;
            } else {
                $data = &$entity['data'] ?? null;
            }

            if (isset($data)) {
                $data = is_scalar($data) ? json_decode($data, (DB::ARR == $fetchMode)) : $data;
            }

            $entities[] = $entity;
        }

        return $entities;
    }

    public static function load(Connection $db, $tableName, int $id, string $fetchMode = DB::OBJ)
    {
        $entities = static::loadMultiple($db, $tableName, [$id], $fetchMode);

        return $entities ? $entities[0] : null;
    }
}
