<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use stdClass;

class LoChecker
{
    private function loData(stdClass $lo)
    {
        if (empty($lo->data)) {
            return [];
        }

        return is_scalar($lo->data) ? json_decode($lo->data, true) : (is_array($lo->data) ? $lo->data : (is_object($lo->data) ? (array) $lo->data : []));
    }

    public function isAuthor(Connection $db, int $loId, int $userId)
    {
        $sql = 'SELECT 1 FROM gc_ro WHERE source_id = ? AND type = ? AND target_id = ?';

        return $db->fetchColumn($sql, [$loId, EdgeTypes::HAS_AUTHOR_EDGE, $userId]) ? true : false;
    }

    public function manualPayment(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data[LoHelper::MANUAL_PAYMENT]) ? ($data[LoHelper::MANUAL_PAYMENT] ? true : false) : false;
    }

    public function manualPaymentRecipient(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data['manual_payment_recipient']) ? $data[LoHelper::MANUAL_PAYMENT_RECIPIENT] : '';
    }

    public function allowReEnrol(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data[LoHelper::ENROLMENT_RE_ENROL]) ? ($data[LoHelper::ENROLMENT_RE_ENROL] ? true : false) : false;
    }
}
