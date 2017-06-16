<?php

namespace go1\util\contract;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\model\Contract;

class ContractHelper
{
    public static function datatable(Connection $db, int $instanceId, string $contractUrl, array $datatableColumns): array
    {
        $datatable = [];
        $sql = 'SELECT * FROM contract WHERE instance_id = ?';
        $q = $db->executeQuery($sql, [$instanceId]);
        while ($row = $q->fetch(DB::OBJ)) {
            $contract = Contract::create($row);
            $datatable[] = self::datatableRow($contract, $datatableColumns, $contractUrl);
        }

        return $datatable;
    }

    public static function datatableRow(Contract $contract, array $columns, string $downloadUrl): array
    {
        $data = [];
        foreach ($columns as $key => $column) {
            if ($key == 'download') {
                $data[$key] = "<a href='{$downloadUrl}/contract/{$contract->getId()}/download'>Download</a>";
            }
            else if (isset($column['callback'])) {
                $data[$key] = call_user_func([$contract, $column['callback']]);
            }

            else {
                $data[$key] = call_user_func([$contract, $column['data']]);
            }
        }

        return $data;
    }
}
