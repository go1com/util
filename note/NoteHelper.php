<?php

namespace go1\util\note;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\group\GroupHelper;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalChecker;

class NoteHelper
{
    private $go1;
    private $dbSocial;

    public static function load(Connection $db, int $id)
    {
        $sql = 'SELECT * FROM gc_note WHERE id = ?';

        return $db->executeQuery($sql, [$id])->fetch(DB::OBJ);
    }

    public static function loadByUUID(Connection $db, string $uuid)
    {
        $sql = 'SELECT * FROM gc_note WHERE uuid = ?';

        return $db->executeQuery($sql, [$uuid])->fetch(DB::OBJ);
    }

    public function setConnection(Connection $go1, Connection $dbSocial)
    {
        $this->go1 = $go1;
        $this->dbSocial = $dbSocial;

        return $this;
    }

    public function loadPortal(string $entityType, int $entityId, PortalChecker $portalChecker)
    {
        $portalId = 0;

        switch ($entityType) {
            case 'lo':
                $lo = LoHelper::load($this->go1, $entityId);
                $lo && ($portalId = $lo->instance_id);
                break;

            case 'group':
                $group = GroupHelper::load($this->dbSocial, $entityId);
                $group && ($portalId = $group->instance_id);
                break;

            default:
                $portalId = $entityId;
                break;

        }

        return $portalChecker->load($this->go1, $portalId);
    }
}
