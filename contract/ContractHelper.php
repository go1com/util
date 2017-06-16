<?php

namespace go1\util\contract;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\model\Contract;

class ContractHelper
{
    public static function datatable(Connection $db, int $instanceId, string $contractUrl): array
    {
        $datatable = [];
        $sql = 'SELECT * FROM contract WHERE instance_id = ?';
        $q = $db->executeQuery($sql, [$instanceId]);
        while ($row = $q->fetch(DB::OBJ)) {
            $contract = Contract::create($row);
            $contract->setDownloadUrl($contractUrl);
            $datatable[] = $contract->datatable();
        }

        return $datatable;
    }
}
