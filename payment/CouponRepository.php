<?php

namespace go1\util\payment;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\Queue;
use Psr\Log\LoggerInterface;

class CouponRepository
{
    private $logger;
    private $db;
    private $queue;

    public function __construct(LoggerInterface $logger, Connection $db, MqClient $queue)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->queue = $queue;
    }

    public function db(): Connection
    {
        return $this->db;
    }

    public function browse(stdClass $user, $instanceId, $entityType = 'lo', $entityId = null, int $limit = 50, int $offset = 0): array
    {
    }

    public function load($idOrCode)
    {
        $column = is_numeric($idOrCode) ? 'id' : 'code';
        $coupon = "SELECT * FROM payment_coupon WHERE {$column} = ?";
        $coupon = $this->db->executeQuery($coupon, [$idOrCode])->fetch(DB::OBJ);

        return $coupon ? Coupon::create($coupon) : false;
    }

    public function create(Coupon &$coupon): int
    {
        $this->db->insert('payment_coupon', $coupon->jsonSerialize());
        $coupon->id = $this->db->lastInsertId('payment_coupon');
        $this->queue->publish($coupon, Queue::COUPON_CREATE);

        return $coupon->id;
    }

    public function update(Coupon $coupon): bool
    {
        if (!$original = $this->load($coupon->id)) {
            return false;
        }

        if (!$diff = $original->diff($coupon)) {
            return false;
        }

        $this->db->update('payment_coupon', $diff, ['id' => $coupon->id]);
        $coupon->original = $original;
        $this->queue->publish($coupon, Queue::COUPON_UPDATE);

        return true;
    }

    public function delete(int $id): bool
    {
        if (!$coupon = $this->load($id)) {
            return false;
        }

        DB::transactional($this->db, function (Connection $db) use (&$coupon) {
            $db->delete('payment_coupon_usage', ['coupon_id' => $coupon->id]);
            $db->executeQuery('DELETE FROM payment_coupon WHERE id = ?', ['id' => $coupon->id]);

            $this->queue->publish($coupon, Queue::COUPON_DELETE);
        });

        return true;
    }

    public function recordUsage(Coupon $coupon, int $transactionId, $userId): int
    {
        $this->db->insert('payment_coupon_usage', $usage = [
            'coupon_id'      => $coupon->id,
            'transaction_id' => $transactionId,
            'user_id'        => $userId,
            'created'        => time(),
        ]);

        $usage['id'] = $this->db->lastInsertId('payment_coupon_usage');
        $this->queue->publish($usage, Queue::COUPON_USE);

        return $usage['id'];
    }

    public function countUsage(Coupon $coupon): int
    {
        return $this->db->fetchColumn('SELECT COUNT(*) FROM payment_coupon_usage WHERE coupon_id = ?', [$coupon->id]);
    }
}
