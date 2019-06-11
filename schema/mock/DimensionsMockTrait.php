<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use go1\util\dimensions\DimensionsHelper;
use go1\util\DB;

trait DimensionsMockTrait
{
    public function createDimension(Connection $db, array $options = [])
    {
        $db->insert('dimensions', [
            'id' => $options['id'] ?? null,
            'parent_id' => $options['parent_id'] ?? null,
            'name' => $options['name'],
            'type' => $options['type'],
            'created_date' => $options['created_date'],
            'modified_date' => $options['modified_date'],
        ]);

        return $db->lastInsertId('dimensions');
    }

    public function createTable(Connection $db)
    {
        DB::install($db, [
            function (Schema $schema) {
                if (!$schema->hasTable('dimensions')) {
                    $table = $schema->createTable('dimensions');
                    $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
                    $table->addColumn('parent_id', 'integer', ['default' => null]);
                    $table->addColumn('name', 'text', ['notnull' => true]);
                    $table->addColumn('type', 'text', ['notnull' => true]);
                    $table->addColumn('created_date', 'datetime', []);
                    $table->addColumn('modified_date', 'datetime', []);
                }
            }
        ]);
    }
}
