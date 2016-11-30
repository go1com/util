<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use Symfony\Component\HttpFoundation\JsonResponse;

class Database
{
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
