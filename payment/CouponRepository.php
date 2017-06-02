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

    public function browse(
        int $instanceId,
        string $entityType = 'lo',
        int $entityId = 0,
        int $userId = null,
        int $status = null,
        int $limit = 50,
        int $offset = 0,
        string $orderBy = null,
        string $direction = 'ASC'): array
    {
        $q = $this
            ->db
            ->createQueryBuilder()
            ->select('coupon.*')
            ->from('payment_coupon', 'coupon')
            ->where('coupon.instance_id = :instance_id')
            ->andWhere('coupon.entity_type = :entity_type')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter(':instance_id', $instanceId)
            ->setParameter(':entity_type', $entityType);

        if (null !== $orderBy) {
            $q->orderBy("coupon.{$orderBy}", $direction);
        }

        if ($entityId) {
            $q
                ->andWhere('coupon.entity_id = :entity_id')
                ->setParameter(':entity_id', $entityId);
        }

        if ($userId) {
            $q
                ->andWhere('coupon.user_id = :user_id')
                ->setParameter(':user_id', $userId);
        }

        if (null !== $status) {
            $q
                ->andWhere('coupon.status = :status')
                ->setParameter(':status', $status);
        }

        $q = $q->execute();
        while ($row = $q->fetch(DB::OBJ)) {
            $rows[] = Coupon::create($row);
        }

        return $rows ?? [];
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
            $db->executeQuery('DELETE FROM payment_coupon_usage WHERE coupon_id = ?', [$coupon->id]);
            $db->executeQuery('DELETE FROM payment_coupon WHERE id = ?', [$coupon->id]);

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

    public function countUsage(Coupon $coupon, int $userId = null): int
    {
        return $userId
            ? $this->db->fetchColumn('SELECT COUNT(*) FROM payment_coupon_usage WHERE coupon_id = ? AND user_id = ?', [$coupon->id, $userId])
            : $this->db->fetchColumn('SELECT COUNT(*) FROM payment_coupon_usage WHERE coupon_id = ?', [$coupon->id]);
    }
}
