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
            'id'            => $options['id'] ?? null,
            'parent_id'     => $options['parent_id'] ?? 0,
            'name'          => $options['name'],
            'type'          => $options['type'],
            'created_date'  => $options['created_date'] ?? date("Y-m-d H:i:s"),
            'modified_date' => $options['modified_date'] ?? date("Y-m-d H:i:s"),
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

    public static function createViews(Connection $db)
    {
        $manager = $db->getSchemaManager();
        $views = $manager->listViews();
        if (!isset($views['dimensions_levels'])) {
            $db->executeQuery(
                'create view dimensions_levels as select a.id as "Level1", b.id as "Level2", c.id as "Level3"
                from dimensions a
                inner join dimensions b on b.parent_id = a.id AND a.parent_id = 0
                left join dimensions c on c.parent_id = b.id;');
        }
    }
}
