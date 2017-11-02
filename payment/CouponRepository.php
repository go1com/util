<?php

namespace go1\util\payment;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;
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
        bool $admin = false,
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
            ->select('DISTINCT coupon.*')
            ->from('payment_coupon', 'coupon')
            ->where('coupon.instance_id = :instance_id')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter(':instance_id', $instanceId);

        if (null !== $orderBy) {
            $q->orderBy("coupon.{$orderBy}", $direction);
        }

        if ($entityId) {
            $q
                ->innerJoin('coupon', 'payment_coupon_item', 'item', 'coupon.id = item.coupon_id')
                ->andWhere('item.entity_id = :entity_id')
                ->andWhere('item.entity_type = :entity_type')
                ->setParameter(':entity_type', $entityType)
                ->setParameter(':entity_id', $entityId);
        }
        elseif (!$admin) {
            $q
                ->andWhere('coupon.user_id = :user_id')
                ->setParameter(':user_id', $userId);
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
        $couponIds = [];
        while ($row = $q->fetch(DB::OBJ)) {
            $rows[] = Coupon::create($row);
            $couponIds[] = $row->id;
        }

        if ($couponIds) {
            $q = $this->db()->executeQuery('SELECT coupon_id, entity_type, entity_id FROM payment_coupon_item WHERE coupon_id IN (?)', [$couponIds], [DB::INTEGERS]);
            while ($row = $q->fetch(DB::OBJ)) {
                foreach ($rows as &$coupon) {
                    if ($coupon->id == $row->coupon_id) {
                        $coupon->add($row->entity_type, $row->entity_id);
                    }
                }
            }
        }

        return $rows ?? [];
    }

    public function load($id)
    {
        $coupon = "SELECT * FROM payment_coupon WHERE id = ?";
        $coupon = $this->db->executeQuery($coupon, [$id])->fetch(DB::OBJ);
        $coupon = $coupon ? Coupon::create($coupon) : false;

        if ($coupon) {
            $q = $this->db->executeQuery('SELECT entity_type, entity_id FROM payment_coupon_item WHERE coupon_id = ?', [$coupon->id]);
            while ($item = $q->fetch(DB::OBJ)) {
                $coupon->add($item->entity_type, $item->entity_id);
            }
        }

        return $coupon;
    }

    public function get($instanceId, $idOrCode, int $userId = null)
    {
        $coupon = "SELECT * FROM payment_coupon WHERE instance_id = ? AND code = ?";
        $coupon = $this->db->executeQuery($coupon, [$instanceId, $idOrCode])->fetch(DB::OBJ);
        $coupon = $coupon ? Coupon::create($coupon) : false;

        if ($coupon) {
            $q = $this->db->executeQuery('SELECT entity_type, entity_id FROM payment_coupon_item WHERE coupon_id = ?', [$coupon->id]);
            while ($item = $q->fetch(DB::OBJ)) {
                $coupon->add($item->entity_type, $item->entity_id);
            }

            if ($userId) {
                $coupon->context['usage'] = [
                    'all' => $this->db->fetchColumn('SELECT COUNT(*) FROM payment_coupon_usage WHERE coupon_id = ?', [$coupon->id]),
                    'my'  => $this->db->fetchColumn('SELECT COUNT(*) FROM payment_coupon_usage WHERE coupon_id = ? AND user_id = ?', [$coupon->id, $userId]),
                ];
            }
        }

        return $coupon;
    }

    public function create(Coupon &$coupon): int
    {
        $row = $coupon->jsonSerialize();
        unset($row['entities']);
        $entities = $coupon->entities ?? [];
        $this->db->insert('payment_coupon', $row);
        $coupon->id = $this->db->lastInsertId('payment_coupon');

        foreach ($entities as $entityType => $entityIds) {
            foreach ($entityIds as $entityId) {
                $this->db->insert('payment_coupon_item', [
                    'coupon_id'   => $coupon->id,
                    'entity_type' => $entityType,
                    'entity_id'   => $entityId,
                ]);
            }
        }

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

        return DB::transactional($this->db, function () use (&$coupon, &$diff, &$original) {
            unset($diff['entities']);
            if ($diff) {
                $diff['updated'] = time();
                $this->db->update('payment_coupon', $diff, ['id' => $coupon->id]);
            }

            $this->db->executeQuery('DELETE FROM payment_coupon_item WHERE coupon_id = ?', [$coupon->id]);

            foreach ($coupon->entities as $entityType => $entityIds) {
                foreach ($entityIds as $entityId) {
                    $this->db->insert('payment_coupon_item', [
                        'coupon_id'   => $coupon->id,
                        'entity_type' => $entityType,
                        'entity_id'   => $entityId,
                    ]);
                }
            }

            $coupon->original = $original;
            $this->queue->publish($coupon, Queue::COUPON_UPDATE);

            return true;
        });
    }

    public function delete(int $id): bool
    {
        if (!$coupon = $this->load($id)) {
            return false;
        }

        DB::transactional($this->db, function (Connection $db) use (&$coupon) {
            $db->executeQuery('DELETE FROM payment_coupon_usage WHERE coupon_id = ?', [$coupon->id]);
            $db->executeQuery('DELETE FROM payment_coupon_item WHERE coupon_id = ?', [$coupon->id]);
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
