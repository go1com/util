<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class LoChecker
{
    private function loData(\stdClass $lo) {
        if (!$lo->data) {
            return [];
        }

        return is_scalar($lo->data) ? json_decode($lo->data, true) : (is_array($lo->data) ? $lo->data : (is_object($lo->data) ? (array) $lo->data : []));
    }

    public function isAuthor(Connection $db, int $loId, int $userId)
    {
        $sql = 'SELECT 1 FROM gc_ro WHERE source_id = ? AND type = ? AND target_id = ?';

        return $db->fetchColumn($sql, [$loId, EdgeTypes::HAS_AUTHOR_EDGE, $userId]) ? true : false;
    }

    public function manualPayment(\stdClass $lo) {
        $data = $this->loData($lo);

        return !empty($data['manual_payment']) ? ($data['manual_payment'] ? true : false) : false;
    }

    public function manualPaymentRecipient(\stdClass $lo) {
        $data = $this->loData($lo);

        return !empty($data['manual_payment_recipient']) ? $data['manual_payment_recipient'] : '';
    }
}
